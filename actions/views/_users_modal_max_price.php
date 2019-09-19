<div class="modal fade max-price-modal-lg" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">

    <div class="modal-content">
      <div class="modal-header">
        <div class="col col-5">
          <h5 class="modal-title">Set max price</h5>
        </div>
        <div class="col col-2">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>

      <div class="modal-body">
          <div class="form-group row">
              <label for="max_price" class="col-3 col-form-label">Max price:</label><br/>
              <div class="col col-9">
                <input type="number" name="max_price" id="max_price" class="form-control" value="0" />
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="max_price_submit" name="Go" class="btn btn-primary" data-dismiss="modal" onclick="changeMaxPrice()">Set max price</button>
      </div>
    </div>
  </div>
</div>

<script>
function changeMaxPrice() {
  $.post(
    'index.php?page=user-change-max-price',
    {
      max_price: $("#max_price").val(),
      user_id: window.currentUserId
    },
    function(data) {
      if(!data.success) {
          return alert(data.error_message || 'There was an error');
      }
      var newMaxPriceText = data.max_price !== null ? data.max_price : 'Set';
      $('#row_' + window.currentUserId + ' .max_price').html(newMaxPriceText);
    }
  );
}
</script>
