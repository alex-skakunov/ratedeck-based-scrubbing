<?php


?>
<!--
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
-->
<div style="text-align: center; margin-bottom: 40px">
  <h1>
      Scrubbing
  </h1>
</div>

<? if(!empty($errorMessage)): new dBug($errorMessage);?>
<? endif;?>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Queue a new file</button><br/><br/>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Queue a new file</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

          <form method="post" id="form" enctype="multipart/form-data" onsubmit="$('#submit').attr('disabled', 'diabled'); $('#loader').show();">
             <input type="hidden" name="version" value="1.0" />

                <div class="form-group row">
                    <label for="file_source" class="col-2 col-form-label">CSV file:</label><br/>

                    <div class="col col-10">
                      <input type="file" name="file_source[]" id="file_source" class="form-control-file" accept=".csv, .txt, .zip, application/zip, text/csv, text/plain" multiple="multiple" /><br/>

                      <? if (!empty($theLastQueuedItem)) : ?>
                      <small style="color:gray">If you don't upload a new file, the "<em><?=$theLastQueuedItem['filename']?></em>" will be used</small>
                      <? endif; ?>


                      <? /*
                      <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                      </div>
                      */ ?>

                    </div>

                </div>

                <div class="form-group row">
                    <label for="max_price" class="col-2 col-form-label">Max price:</label>
                    <div class="col col-10">
                      <input type="text" name="max_price" id="max_price" class="form-control" pattern="[0-9\.]+" value="" style="width: 10em">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">DNC:</label>
                    <div class="col col-10" style="text-align: left;">
                        <label for="lawsuits_dnc">
                          <input type="checkbox" name="include_lawsuits_dnc" id="lawsuits_dnc" value="1" checked="checked" /> Lawsuits DNC
                        </label><br/>
                        <label for="master_dnc">
                          <input type="checkbox" name="include_master_dnc" id="master_dnc" value="1"/> Master DNC
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                  <div class="col col-2">Type:</div>
                  <div class="col col-10" style="text-align: left;">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input class="form-check-input" type="checkbox" name="wireless" value="1" id="wireless" checked="checked"> Wireless
                        </label>
                      </div>

                      <div class="form-check">
                        <label class="form-check-label">
                          <input class="form-check-input" type="checkbox" name="landline" value="1" id="landline" checked="checked"> Landline
                        </label>
                      </div>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col col-2">States:</div>
                  <div class="col col-10" style="text-align: left;">

                      <div class="form-check">
                        <label class="form-check-label">
                          <input class="form-check-input" type="checkbox" name="areacodes_all" value="1" id="areacodes_all" <?=!empty($areacodes_all) ? 'checked="checked"' : ''?> onclick="toggleStatesSelector()"> Include all
                        </label>
                      </div>


                        <div  class="form-group" id="states_list" style="height: 12em; width: 20em; border: solid 1px gray; overflow: scroll; text-align: left; padding: 5px 15px">
                        <?php
                          foreach($areacodeList as $token => $areacode) :
                        ?>
                            <div class="form-check">
                              <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="areacode[]" value="<?=$token?>" id="<?=$token?>" checked="checked" disabled="disabled"> <?=$areacode?>
                              </label>
                            </div>
                        <?php
                          endforeach;
                        ?>
                        </div>

                    <a href="#" onclick="$('div#states_list input').each(function() {$(this).attr('checked', 'checked');}); return false;"><small>select all</small></a> /
                    <a href="#" onclick="$('div#states_list input').each(function() {$(this).removeAttr('checked');}); return false;"><small>select none</small></a>
                  </div>
                </div>

          </form>

      </div>
      <div class="modal-footer">
        <button type="button" id="submit" name="Go" class="btn btn-primary" data-dismiss="modal" onclick="$('#form').submit();">Queue the job</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function toggleStatesSelector() {
  var selected = $('#areacodes_all').prop('checked');
  $('div#states_list input').each(function() {
    selected ? $(this).attr('disabled', 'disabled') : $(this).removeAttr('disabled');
  })
  $('div#states_list label').each(function() {
    selected ? $(this).addClass('disabled') : $(this).removeClass('disabled');
  })
}
</script>

<? if(empty($recordset)) {
  return;
}
?>

<table class="table table-striped">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Filename</th>
      <th scope="col">Max price</th>
      <th scope="col">DNC list</th>
      <th scope="col">Type</th>
      <th scope="col">States</th>
      <th scope="col">—</th>
    </tr>
  </thead>
  <tbody id="table-body">
      <? foreach ($recordset as $row) : ?>
        <tr>
          <th scope="row"><?=$row['id']?></th>
          <td><?=$row['filename']?>
              <?if(!empty($row['rows_count'])):?>
                  <br/><small class="text-muted">(<?=number_format($row['rows_count'])?> rows)</small>
              <? endif; ?>
          </td>
          <td><?=$row['max_price']?></td>
          <td><small><?
          $list = [];
          if (!empty($row['include_lawsuits_dnc'])) {
            $list[] = 'Lawsuits';
          }
          if (!empty($row['include_master_dnc'])) {
            $list[] = 'Master';
          }
          echo !empty($list) ? implode(', ', $list) : '—';
          ?></small></td>
          <td><?
            $list = array();
            if(!empty($row['include_wireless_type'])) {
                $list[] = 'Wireless';
            }
            if(!empty($row['include_landline_type'])) {
                $list[] = 'Landline';
            }
            echo implode(' + ', $list);
          ?></td>
          <td>
              <? 
                  if(empty($row['specific_states_list'])) {
                      echo 'All';
                  }
                  else {
                      $list = array();
                      foreach (explode(',', $row['specific_states_list']) as $token) {
                          $_token = trim(strtolower($token));
                          if(empty($areacodeList[$_token])) {
                              continue;
                          }
                          $list[] = $areacodeList[$_token];
                      }
                      if (sizeof($list) < 4) {
                          echo '<em>', implode(', ', $list), '</em>';
                      }
                      else {
                          echo '<em title="', implode(', ', $list),'">', current($list), ' and ', sizeof($list)-1, ' more</em>'; 
                      }
                  }
            ?>
          </td>
          <td>
            <?
            switch ($row['status']) {
                case 'success':
                    if ($row['final_rows_count']) {
                      $url = 'temp/csv/'. $row['id'] . '.csv';
                      echo '<a href="'.$url.'">Download CSV</a>';
                      if (!empty($row['final_rows_count'])) {
                        echo '<br/><small class="text-muted">(' . number_format($row['final_rows_count']) . ' rows)</small>';
                      }
                    }
                    else {
                        echo '<small class="text-muted">Empty result.</small>';
                    }
                    break;

                case 'error':
                    echo 'Error: ' . $row['error_message'];
                    break;
                
                default:
                    echo '<em>', ucfirst($row['status']), '</em>';
                    if (!empty($row['progress']) && $row['progress'] < 100) {
                        echo ' <small class="text-muted">(', number_format($row['progress']), '%)</small>';
                    }
            }
            ?>
          </td>
        </tr>
      <? endforeach; ?>
  </tbody>
</table>

<p class="text-muted"><small><a href="?page=scrubbing&erase_queue=1" onclick="return confirm('Are you sure you want to delete all these records?')">Click here to erase the queue</a></small></p>


