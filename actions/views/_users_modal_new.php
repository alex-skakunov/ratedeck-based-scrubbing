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
          <form method="post" id="form" enctype="multipart/form-data" onsubmit="$('#submit').attr('disabled', 'disabled'); $('#loader').show();">
          <input type="hidden" name="action" value="add_user" />

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