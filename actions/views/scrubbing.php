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
                      <input type="file" name="file_source[]" id="file_source" class="form-control-file" accept=".csv, .xls, .xlsx, .txt, .zip, application/zip, text/csv, text/plain" multiple="multiple" /><br/>

                      <? if (!empty($theLastQueuedItem)) : ?>
                      <small style="color:gray">If you don't upload a new file, the "<em><?=$theLastQueuedItem['filename']?></em>" will be used</small>
                      <? endif; ?>
                    </div>
                </div>

                <? if ($isAdmin) : ?>
                <div class="form-group row">
                    <label for="max_price" class="col-2 col-form-label">Max price:</label>
                    <div class="col col-10">
                      <input type="text" name="max_price" id="max_price" class="form-control" pattern="[0-9\.]+" value="" style="width: 10em">
                    </div>
                </div>
                <? endif; ?>

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
                    <div class="col col-2">Order:</div>
                    <div class="col col-10" style="text-align: left;">
                        <label for="sort_order">
                          <select name="sort_order" id="sort_order">
                            <option value="1">As is</option>
                            <option value="2">Asceding</option>
                            <option value="3">Descending</option>
                            <option value="4">Random</option>
                          </select>
                        </label>
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
    var elements = $('form input[type=text], form input[type=checkbox]');
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

function save_phone_column(id) {
    var selectedIndex = $('#columns_' + id).val();
    var downloadScope = $("input[name='download_scope_"+id+"']:checked"). val();
    if ("" == selectedIndex) {
      return;
    }
    $('#status_' + id + ' select, #status_' + id + ' input').attr('disabled', 'disabled');
    $.post('index.php?page=save-phone-column', {
        id: id,
        value: selectedIndex,
        download_scope: downloadScope
      },
      function(data) {
          if(!data.success) {
               return alert('Could not save this data');
         }
         $('#status_' + id).html('<em>Queued</em>');
      }
    );
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
      
      <? if ($isAdmin) : ?>
      <th scope="col">Max price</th>
      <? endif; ?>

      <th scope="col">DNC list</th>
      <th scope="col">Type</th>
      <th scope="col">States</th>
      <th scope="col">Order</th>
      <th scope="col">—</th>
    </tr>
  </thead>
  <tbody id="table-body">
      <? $i = sizeof($recordset); ?>
      <? foreach ($recordset as $row) : ?>
        <?
          $downloadHint = '<br/><small class="text-muted">('
            . (!empty($row['columns_list'])
              ? ('file' == $row['download_scope'] ? 'Whole file' : 'Column #' . (1 + $row['selected_column_index']) . ' only')
              : 'A single-column file')
            . ')</small>';

        ?>
        <tr>
          <th scope="row"><?=$i--?></th>
          <td><?=$row['filename']?>
              <?if(!empty($row['rows_count'])):?>
                  <br/><small class="text-muted">(<?=number_format($row['rows_count'])?> rows)</small>
              <? endif; ?>
          </td>

          <? if ($isAdmin) : ?>
          <td><?=$row['max_price']?></td>
          <? endif; ?>

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
          <td><?=ucfirst($row['sort_order'])?></td>
          <td id="status_<?=$row["id"]?>" align="center">
            <?
            switch ($row['status']) {
                case 'success':
                    if ($row['final_rows_count']) {
                      $url = 'index.php?page=download-csv&id='. $row['id'];
                      echo '<a href="'.$url.'">Download CSV</a>';
                      if (!empty($row['final_rows_count'])) {
                        echo ' <small class="text-muted">(' . number_format($row['final_rows_count']) . ' rows)</small>';
                      }
                      echo $downloadHint;
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
                
                case 'action required':
                    $list = (array)json_decode($row['columns_list'], 1);
                    if (empty($list)) {
                        echo 'Error. Something went wrong.';
                        break;
                    }
                    echo '<select id="columns_'. $row["id"] . '" style="margin-bottom: 20px">';
                    echo '<option value="">- choose phone column -</option>';
                    $i = 0;
                    foreach($list as $column) {
                        echo '<option value="' . $i++ . '">' . $column . '</option>';
                    }
                    echo '</select><br/>';
                    echo '<b>Then download:</b><br/>';
                    echo '<input type="radio" value="column" name="download_scope_'. $row["id"] . '" id="download_column_'. $row["id"] . '" checked="checked" /> <label for="download_column_'. $row["id"] . '">This column</label><br/>';
                    echo '<input type="radio" value="file" name="download_scope_'. $row["id"] . '" id="download_all_'. $row["id"] . '" /> <label for="download_all_'. $row["id"] . '">Whole file</label><br/>';
                    echo '<input type="button" value="Save" onclick="save_phone_column('.$row['id'].')" />';
                    break;

                default:
                    echo '<em>', ucfirst($row['status']), '</em>';
                    echo $downloadHint;
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