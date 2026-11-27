<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Customer relationships</span>
      <h2>Registered customers</h2>
    </div><span><?= count($customers ?? []) ?> profiles</span>
  </div>
  <form class="admin-toolbar" method="get" action="index.php"><input type="hidden" name="page" value="customers"><label class="sr-only" for="customer-search">Search customers</label><input id="customer-search" name="q" value="<?= h($customerSearch ?? '') ?>" placeholder="Name, email, or phone"><button class="button button-dark" type="submit">Search</button><?php if (!empty($customerSearch)): ?><a class="text-link" href="?page=customers">Clear</a><?php endif; ?></form>
  <div class="admin-table-wrap">
    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Phone</th>
          <th>Orders</th>
          <th>Verified spend</th>
          <th>Joined</th>
          <th>Account</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($customers ?? []) as $customer): ?><tr>
            <td><a href="?page=orders&amp;q=<?= rawurlencode($customer['phone_normalized']) ?>"><strong><?= h($customer['full_name']) ?></strong></a><small class="table-secondary"><?= h($customer['email'] ?: 'No email') ?></small></td>
            <td><?= h($customer['phone_normalized']) ?></td>
            <td><?= (int) $customer['order_count'] ?></td>
            <td><?= money($customer['verified_spend']) ?></td>
            <td><?= h(date('j M Y', strtotime($customer['created_at']))) ?></td>
            <td>
              <form action="../api/index.php" method="post" data-api-form data-success="Customer account updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.customer.toggle"><input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>"><input type="hidden" name="is_active" value="<?= $customer['is_active'] ? 0 : 1 ?>"><button class="table-action" type="submit"><?= $customer['is_active'] ? 'Disable' : 'Enable' ?></button></form>
            </td>
          </tr><?php endforeach; ?>
        <?php if (empty($customers)): ?><tr>
            <td colspan="6">
              <div class="admin-empty">No registered customers match this search.</div>
            </td>
          </tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Guest checkout</span>
      <h2>Guest customers</h2>
    </div><span><?= count($guestCustomers ?? []) ?> phone records</span>
  </div>
  <p class="admin-note">Guest history is grouped by normalized phone number so repeat checkouts remain traceable without requiring an account.</p>
  <div class="admin-table-wrap">
    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Phone</th>
          <th>Orders</th>
          <th>Verified spend</th>
          <th>Most recent order</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($guestCustomers ?? []) as $customer): ?><tr>
            <td><a href="?page=orders&amp;q=<?= rawurlencode($customer['phone_normalized']) ?>"><strong><?= h($customer['full_name']) ?></strong></a><small class="table-secondary"><?= h($customer['email'] ?: 'No email') ?></small></td>
            <td><?= h($customer['phone_normalized']) ?></td>
            <td><?= (int) $customer['order_count'] ?></td>
            <td><?= money($customer['verified_spend']) ?></td>
            <td><?= h(date('j M Y', strtotime($customer['last_order_at']))) ?></td>
          </tr><?php endforeach; ?>
        <?php if (empty($guestCustomers)): ?><tr>
            <td colspan="5">
              <div class="admin-empty">No guest orders match this search.</div>
            </td>
          </tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>