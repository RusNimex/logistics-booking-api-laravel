import http from 'k6/http';
import exec from 'k6/execution';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://app:8000/api';
const SLOT_COUNT = Number(__ENV.SLOT_COUNT || 500);
const VUS = Number(__ENV.VUS || 100);
const DURATION = __ENV.DURATION || '15m';
const IDEMPOTENCY_RATE = Number(__ENV.IDEMPOTENCY_RATE || 5);
const IDEMPOTENCY_SLOT = Number(__ENV.IDEMPOTENCY_SLOT || 1);
const IDEMPOTENCY_KEY = __ENV.IDEMPOTENCY_KEY || '11111111-1111-1111-1111-111111111111';

export const options = {
  scenarios: {
    fill_slots: {
      executor: 'constant-vus',
      vus: VUS,
      duration: DURATION,
      exec: 'createHold',
    },
    monitor: {
      executor: 'constant-vus',
      vus: 1,
      duration: DURATION,
      exec: 'monitorSlots',
    },
    idempotency: {
      executor: 'constant-arrival-rate',
      rate: IDEMPOTENCY_RATE,
      timeUnit: '1s',
      duration: DURATION,
      preAllocatedVUs: 5,
      maxVUs: 50,
      exec: 'idempotencyBurst',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.1'],
  },
};

http.setResponseCallback(http.expectedStatuses({ min: 200, max: 399 }, 404, 409));

function randomSlotId() {
  return Math.floor(Math.random() * SLOT_COUNT) + 1;
}

function uuid4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

export function createHold() {
  const slotId = randomSlotId();
  const idempotencyKey = uuid4();

  const res = http.post(
    `${BASE_URL}/slots/${slotId}/hold`,
    null,
    { headers: { 'Idempotency-Key': idempotencyKey } }
  );

  check(res, {
    'hold created or conflict': (r) => r.status === 200 || r.status === 409,
  });

  if (res.status === 200) {
    const holdId = res.json('data.hold_id') ?? res.json('hold_id');

    if (holdId) {
      const confirmRes = http.post(`${BASE_URL}/holds/${holdId}/confirm`);
      const confirmBody = confirmRes.body || '';

      check(confirmRes, {
        'hold confirmed': (r) => r.status === 200,
        'oversale detected': () =>
          confirmRes.status === 409 &&
          confirmBody.includes('oversale'),
      });
    }
  }

  sleep(0.05);
}

export function monitorSlots() {
  const res = http.get(`${BASE_URL}/slots/availability`);

  if (!check(res, { 'availability 200': (r) => r.status === 200 })) {
    sleep(1);
    return;
  }

  const data = res.json('data') || [];
  const totalRemaining = data.reduce(
    (sum, slot) => sum + Number(slot.remaining || 0),
    0
  );

  if (totalRemaining <= 0) {
    exec.test.abort('All slots are full');
  }

  sleep(1);
}

export function idempotencyBurst() {
  const res = http.post(
    `${BASE_URL}/slots/${IDEMPOTENCY_SLOT}/hold`,
    null,
    { headers: { 'Idempotency-Key': IDEMPOTENCY_KEY } }
  );

  check(res, {
    'idempotent 200/409': (r) => r.status === 200 || r.status === 409,
  });

  sleep(0.2);
}
