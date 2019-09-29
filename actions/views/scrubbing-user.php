<?php
$templatesHashedList = array();
foreach($templates as $template) {
    $templatesHashedList[$template['id']] = json_decode($template['settings'], 1);
}

?>
<script>
var templatesData = <?=json_encode($templatesHashedList);?>;
</script>
<!--
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
-->
<div style="text-align: center; margin-bottom: 40px">
  <h1>
      Scrubbing
  </h1>
</div>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Queue a new file</button><br/><br/>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">

    <div class="modal-content">
      <div class="modal-header">
        <div class="col col-5">
          <h5 class="modal-title">Queue a new file</h5>
        </div>
        <div class="col col-5">
           <select name="template" style="width: 13em">
               <optgroup>
                   <option value="0" selected>- choose or save client -</option>
                   <option value="">Save as new...</option>
               </optgroup>
               <? if (!empty($templates)) : ?>
                   <? foreach ($templates as $template) : ?>
                   <option value="<?=$template['id']?>"><?=$template['title']?></option>
                   <? endforeach; ?>
               <? endif; ?>
           </select>
           <a href="#" id="delete_client" style="display: none;" onclick="deleteClient();"><small>Delete this client</small></a>
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
                    <label for="file_source" class="col-2 col-form-label">CSV file:</label><br/>

                    <div class="col col-10">
                      <input type="file" name="file_source[]" id="file_source" class="form-control-file" accept=".csv, .tsv, .xls, .xlsx, .txt, .zip, application/zip, text/csv, text/plain" multiple="multiple" /><br/>

                      <? if (!empty($theLastQueuedItem)) : ?>
                      <small style="color:gray">If you don't upload a new file, the "<em><?=$theLastQueuedItem['filename']?></em>" will be used</small>
                      <? endif; ?>

                    </div>

                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">DNC:</label>
                    <div class="col col-10" style="text-align: left;">
                        <? foreach($blacklistsList as $token) : ?>
                            <label for="<?=$token?>_dnc">
                                <input type="checkbox" name="include_<?=$token?>_dnc" id="<?=$token?>_dnc" value="1" checked="checked" /> <?=ucfirst($token)?> DNC
                            </label><br/>
                        <? endforeach; ?>
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
                          <input class="form-check-input" type="checkbox" name="areacodes_all" value="1" id="areacodes_all" <?=!empty($areacodes_all) ? 'checked="checked"' : ''?> > Include all
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

                    <a href="#" onclick="$('div#states_list input').each(function() {$(this).prop('checked', 1); $('form').attr('dirty', 1);}); return false;"><small>select all</small></a> /
                    <a href="#" onclick="$('div#states_list input').each(function() {$(this).prop('checked', 0); $('form').attr('dirty', 1);}); return false;"><small>select none</small></a>
                  </div>
                </div>

                <div class="form-group row">
                    <div class="col-2"></div>
                    <div class="col col-10" style="text-align: left;">
                        <label for="is_blacklisted_report_required">
                          <input type="checkbox" name="is_blacklisted_report_required" id="is_blacklisted_report_required" value="1" /> Export DNC records matched
                        </label>
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
$('#areacodes_all').click(syncAllStatesSelector);
function syncAllStatesSelector() {
  var selected = $('#areacodes_all').prop('checked');
  $('div#states_list input').each(function() {
    selected ? $(this).attr('disabled', 'disabled') : $(this).removeAttr('disabled');
  })
  $('div#states_list label').each(function() {
    selected ? $(this).addClass('disabled') : $(this).removeClass('disabled');
  })
}

$('input[type=text], input[type=checkbox], input[type=radio]').change(function(){
    var id = $('select').val();
    if (0 == id || '' == id) {
        return;
    }

    $('form').attr('dirty', '1');
});

setInterval(function() {
    var id = $('select').val();
    if (0 == id || "" == id) {
        return;
    }

    if (1 != $('form').attr('dirty')) {
        return;
    }
    var list = collectFormData();
    
    $('form').removeAttr('dirty');
    templatesData[id] = list;
    $.post('index.php', {
      page: 'template',
      method: 'update',
      fields: list,
      id: id
    }); 
}, 1000);

function collectFormData() {
    var list = {};
    var elements = $('input[type=text], input[type=checkbox]');
    if (!elements.length) {
        return;
    }

    for(var i = 0; i < elements.length; i++) {
        var elem = $(elements[i]);
        if ('checkbox' == elem.attr('type')) {
            list[elem.attr('id')] = elem.prop('checked') ? 1 : 0;
        }
        else {
            list[elem.attr('id')] = elem.val();
        }
    }
    return list;
}

function showSaveClientDialog() {
    var title = prompt('Enter a new client title');
    if (!title) {
        return;
    }
    var list = collectFormData();
    $.post('index.php', {
        page: 'template',
        method: 'create',
        fields: list,
        title: title
      },
      function(data) {
          if(!data.id) {
               return alert('Could not save the client');
         }
         templatesData[data.id] = list;
         $('select').append($("<option></option>")
                    .attr("value", data.id)
                    .text(title));
         $('select').val(data.id);
      }
    );
}

function deleteClient() {
    var id = $('select').val();
    if (!id) {
        return;
    }

    if (!confirm('Are you sure you want to delete this client?')) {
        return;
    }

    $.post('index.php', {
      page: 'template',
      method: 'delete',
      id: id
    });

    $('select option[value="' + id + '"]').remove();
    $('select').val(0);
    $('#delete_client').hide();
}

$('select').change(function() {
  $('#delete_client').hide();
  var id = $(this).val();
  if ("0" === id) {
    return;
  }

  if ("" === id) {
    return showSaveClientDialog();
  }

  if (!templatesData[id]) {
    return;
  }
  applySettings(templatesData[id]);
  syncAllStatesSelector();
  $('#delete_client').show();
});

function applySettings(settings) {
  for(var id in settings) {
    var value = settings[id];
    var elem = $('#' + id);
    if (!elem.length) {
      continue;
    }

    switch (elem.attr('type')) {
        case 'checkbox':
            if (!parseInt(value)) {
                elem.prop('checked', 0);
            }
            else {
                elem.prop('checked', 1);
            }
            break;
        default:
            elem.val(value);
    }
  }
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
      <th scope="col">DNC list</th>
      <th scope="col">Type</th>
      <th scope="col">States</th>
      <th scope="col">—</th>
    </tr>
  </thead>
  <tbody id="table-body">
      <? $i = sizeof($recordset); ?>
      <? foreach ($recordset as $row) : ?>
        <tr>
          <th scope="row"><?=$i--?></th>
          <td><?=$row['filename']?>
              <?if(!empty($row['rows_count'])):?>
                  <br/><small class="text-muted">(<?=number_format($row['rows_count'])?> rows)</small>
              <? endif; ?>
          </td>
          <td><small><?
          $list = [];
          foreach($blacklistsList as $token) {
              if (!empty($row['include_' . $token . '_dnc'])) {
                  $list[] = ucfirst($token);
              }
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
          <td align="left">
            <?
            switch ($row['status']) {
                case 'success':
                    if ($row['final_rows_count']) {
                      $url = 'index.php?page=download-csv&id='. $row['id'];
                      echo '<a href="'.$url.'">Download CSV</a>';
                      if (!empty($row['final_rows_count'])) {
                        echo ' <small class="text-muted">(' . number_format($row['final_rows_count']) . ' rows)</small>';
                      }
                    }
                    else {
                        echo '<small class="text-muted">Empty result.</small>';
                    }

                    if (!empty($row['is_blacklisted_report_required'])) {
                      foreach ($blacklistsList as $blacklistName) {
                          if (empty($row['include_' . $blacklistName . '_dnc'])) {
                              continue;
                          }
                          $url = 'index.php?page=download-csv&blacklist='.$blacklistName.'&id='. $row['id'];
                          echo '<br/><small>' . ucfirst($blacklistName) . ' DNC: </small>';
                          switch($row['blacklist_'.$blacklistName.'_rows_count']) {
                            case null:
                              echo '<small class="text-muted">Calculating...</small>';
                              break;
                            case 0:
                              echo '<small class="text-muted">Empty.</small>';
                              break;
                            default:
                              echo ' <a href="'.$url.'"><small>Download</small></a> <small class="text-muted">(' . number_format($row['blacklist_'.$blacklistName.'_rows_count']) . ' rows)</small>';
                          }
                      }
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