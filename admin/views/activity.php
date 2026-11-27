<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">A record of studio changes</span>
            <h2>Recent activity</h2>
        </div><span>Latest 100 entries</span>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Administrator</th>
                    <th>Action</th>
                    <th>Record</th>
                    <th>Details</th>
                    <th>IP address</th>
                </tr>
            </thead>
            <tbody><?php foreach (($activityLogs ?? []) as $entry): ?><tr>
                        <td><?= h(date('j M Y · H:i', strtotime($entry['created_at']))) ?></td>
                        <td><?= h($entry['admin_name'] ?? 'System') ?></td>
                        <td><code><?= h($entry['action']) ?></code></td>
                        <td><?= h($entry['entity_type'] ?? '—') ?><?= $entry['entity_id'] ? ' #' . (int) $entry['entity_id'] : '' ?></td>
                        <td><code><?= h($entry['details'] ?? '—') ?></code></td>
                        <td><?= h($entry['ip_address'] ?? '—') ?></td>
                    </tr><?php endforeach; ?><?php if (empty($activityLogs)): ?><tr>
                        <td colspan="6">
                            <div class="admin-empty">Studio actions will appear here as they are recorded.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>