<?php

namespace App\Reports;

/**
 * Which order / item statuses each report counts (ids from order_statuses).
 */
final class ReportStatus
{
    public const SOLD = 3;        // Packed/Shipped

    public const CANCELLED = 6;

    public const RETURNED = 4;

    /**
     * Orders that came back. Item actions (return to warehouse, claim, refund, reject) also
     * change the parent order's status, so a returned order can sit at any of these.
     */
    public const RETURNED_ORDER = [4, 9, 10, 11, 12];

    /** What happened to a returned item (order_details.status_id; null = not handled yet) */
    public const ITEM_OUTCOMES = [
        'pending' => 'Awaiting action',
        9         => 'Returned to warehouse',
        10        => 'For claims',
        11        => 'Refunded',
        12        => 'Claim rejected',
    ];

    public static function itemOutcome($statusId): string
    {
        return self::ITEM_OUTCOMES[$statusId ?? 'pending'] ?? 'Awaiting action';
    }
}
