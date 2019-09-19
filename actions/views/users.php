<div style="text-align: center; margin-bottom: 40px">
  <h1>
      Users
  </h1>
</div>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".add-user-modal-lg">Add a user</button><br/><br/>

<?
include_once '_users_modal_new.php';
include_once '_users_modal_max_price.php';
?>

<table class="table table-striped">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Email address</th>
      <th scope="col">Last login</th>
      <th scope="col">Is admin?</th>
      <th scope="col">Max price</th>
      <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody id="table-body">
      <? foreach ($usersList as $user) : ?>
        <tr id="row_<?=$user['id']?>">
          <th scope="row"><?=$user['id']?></th>
          <td><?=$user['name']?></td>
          <td><?=$user['email']?></td>
          <td><small class="text-muted"><?=!empty($user['last_login_at']) ? $user['last_login_at'] : '<small class="text-muted">—</small>' ?></small></td>
          <td><?=$user['is_admin'] ? 'Yes' : '<small class="text-muted">—</small>'?></td>
          <td>
            <small>
              <a class="max_price" href="#" onclick="window.currentUserId = <?=$user['id']?>; return false;" data-toggle="modal" data-target=".max-price-modal-lg">
                <?=!empty($user['max_price']) ? number_format($user['max_price'], 2) : 'Set'?><br/>
              </a>
            </small>
          </td>
          <td><a href="#" onclick="if (confirm('Are you sure this user should be deleted?')) {deleteUser(this, <?=$user['id']?>);} return false;">&times;</a></td>
        </tr>
      <? endforeach; ?>
  </tbody>
</table>

<script>
function deleteUser(link, id) {
    var rowId = id;
    $.post('index.php?page=user-delete', {
        id: id
      },
      function() {
        console.log('killed!', rowId);
        $('#row_' + rowId).remove();
      }
    );
}
</script>