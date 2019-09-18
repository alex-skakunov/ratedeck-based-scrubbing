<div style="text-align: center; margin-bottom: 40px">
  <h1>
      Users
  </h1>
</div>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".add-user-modal-lg">Add a user</button><br/><br/>

<div class="modal fade add-user-modal-lg" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">

    <div class="modal-content">
      <div class="modal-header">
        <div class="col col-5">
          <h5 class="modal-title">Add a new user</h5>
        </div>
        <div class="col col-2">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>

      <div class="modal-body">
          <form method="post" id="form" enctype="multipart/form-data" onsubmit="$('#submit').attr('disabled', 'diabled'); $('#loader').show();">
             <input type="hidden" name="version" value="1.0" />

              <div class="form-group row">
                  <label for="email" class="col-2 col-form-label">Email:</label><br/>
                  <div class="col col-10">
                    <input type="email" name="email" id="email" class="form-control" />
                  </div>
              </div>

              <div class="form-group row">
                  <label for="password" class="col-2 col-form-label">Password:</label>
                  <div class="col col-10">
                    <input type="text" name="password" id="password" class="form-control" pattern="[a-zA-Z0-9\_\.]+" value="" sstyle="width: 10em">
                  </div>
              </div>

              <div class="form-group row">
                  <label for="name" class="col-2 col-form-label">Name:</label>
                  <div class="col col-10">
                    <input type="text" name="name" id="name" class="form-control" value="" sstyle="width: 10em">
                  </div>
              </div>

              <div class="form-group row">
                <div class="col col-2"></div>
                <div class="col col-10" style="text-align: left;">
                    <div class="form-check">
                      <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin"> Is an admin?
                      </label>
                    </div>
                </div>
              </div>

          </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="submit" name="Go" class="btn btn-primary" data-dismiss="modal" onclick="$('#form').submit();">Add the guy</button>
      </div>
    </div>
  </div>
</div>
<table class="table table-striped">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Email address</th>
      <th scope="col">Last login</th>
      <th scope="col">Is admin?</th>
      <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody id="table-body">
      <? foreach ($usersList as $user) : ?>
        <tr id="row_<?=$user['id']?>">
          <th scope="row"><?=$user['id']?></th>
          <td><?=$user['name']?></td>
          <td><?=$user['email']?></td>
          <td><small class="text-muted"><?=!empty($user['last_login_at']) ? $user['last_login_at'] : '' ?></small></td>
          <td><?=$user['is_admin'] ? 'Yes' : '—'?></td>
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