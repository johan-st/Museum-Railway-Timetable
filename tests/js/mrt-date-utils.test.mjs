import { test, describe } from 'node:test';
import assert from 'node:assert/strict';
import { loadAssetInWindow } from './load-asset.mjs';

describe('MRTDateUtils', () => {
    const { window } = loadAssetInWindow('mrt-date-utils.js');
    const DU = window.MRTDateUtils;

    test('formatYmdForDisplay with monthNames array', () => {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        assert.equal(DU.formatYmdForDisplay('2026-06-15', months), 'Jun 15, 2026');
    });

    test('formatYmdForDisplay accepts cfg with monthNames', () => {
        const cfg = {
            monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        };
        assert.equal(DU.formatYmdForDisplay('2026-03-10', cfg), 'Mar 10, 2026');
    });

    test('formatYmdForDisplay rejects invalid month in string', () => {
        assert.equal(DU.formatYmdForDisplay('2026-13-01', ['Jan']), '2026-13-01');
    });

    test('ymdFromParts pads month and day', () => {
        assert.equal(DU.ymdFromParts(2026, 6, 7), '2026-06-07');
    });

    test('ymdFromParts returns empty for non-finite parts', () => {
        assert.equal(DU.ymdFromParts('x', 1, 1), '');
    });

    test('addCalendarMonths rolls year', () => {
        assert.deepEqual(JSON.parse(JSON.stringify(DU.addCalendarMonths(2026, 12, 1))), { year: 2027, month: 1 });
        assert.deepEqual(JSON.parse(JSON.stringify(DU.addCalendarMonths(2026, 1, -1))), { year: 2025, month: 12 });
    });

    test('validateHhMm empty is valid', () => {
        assert.equal(DU.validateHhMm(''), true);
        assert.equal(DU.validateHhMm('   '), true);
    });

    test('validateHhMm accepts 09:15 and rejects 25:00', () => {
        assert.equal(DU.validateHhMm('09:15'), true);
        assert.equal(DU.validateHhMm('25:00'), false);
    });
});
