<?php

$areacodeList = array(
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Canada',
  'Colorado',
  'Connecticut',
  'Delaware',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Islands',
  'Virginia',
  'Washington',
  'Washington, DC',
  'West Virginia',
  'Wisconsin',
  'Wyoming'
);
?>
<!--
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
-->
<div style="text-align: center; margin-bottom: 10px;">
	<h1>
  		Do the scrubbing
	</h1>
</div>


<form method="post" enctype="multipart/form-data" onsubmit="$('#submit').attr('disabled', 'diabled'); $('#loader').show();">
   <input type="hidden" name="version" value="1.0" />
   <table border="0" align="center">
    <tr>
      <td>
      	<label for="file_source">CSV file to do scrubbing:</label><br/>
      </td>
      <td rowspan="8" width="10px">&nbsp;</td>
      <td>
      	<input type="file" name="file_source" id="file_source" class="edt" value="<?=$file_source?>" " accept=".csv, .txt, .zip, application/zip, text/csv, text/plain" /><br/>
      	<small style="color:gray">(Leave this field empty to re-use the data from previous upload)</small>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><label for="max_price">Max price:</label></td>
      <td>
      	<input type="text" name="max_price" id="max_price" class="edt"  pattern="[0-9\.]+" value="<?=$max_price?>" style="width: 10em">
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right">Type:</td>
      <td align="left">
      	<input type="checkbox" name="wireless" value="1" id="wireless" <?=!empty($wireless) ? 'checked="checked"' : ''?>/>
      	<label for="wireless">Wireless</label><br/>

      	<input type="checkbox" name="landline" value="1" id="landline" <?=!empty($landline) ? 'checked="checked"' : ''?>/>
      	<label for="landline">Landline</label>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right" valign="top">States:</td>
      <td align="left">
        <input type="checkbox" name="areacodes_all" value="1" id="areacodes_all" <?=!empty($areacodes_all) ? 'checked="checked"' : ''?> onclick="toggleStatesSelector()"/>
        <label for="areacodes_all">Include all</label><br/><br/>

        <div id="states_list" style="height: 15em; border: solid 1px gray; overflow: scroll;">
          <?php
            foreach($areacodeList as $areacode) :
              $token = str_replace(' ', '_', strtolower($areacode));
          ?>
            <input type="checkbox" name="areacode[]" value="<?=$token?>" id="<?=$token?>" <?=empty($areacodes) || !empty($areacodes[$token]) ? 'checked="checked"' : ''?> <?=!empty($areacodes_all) ? 'disabled="disabled"' : ''?>/>
            <label for="<?=$token?>" <?=!empty($areacodes_all) ? 'class="disabled"' : ''?>><?=$areacode?></label><br/>
          <?php
            endforeach;
          ?>
        </div>
        <a href="#" onclick="$('div#states_list input').each(function() {$(this).attr('checked', 'checked');}); return false;"><small>select all</small></a> /
        <a href="#" onclick="$('div#states_list input').each(function() {$(this).removeAttr('checked');}); return false;"><small>select none</small></a>
      </td>
    </tr>

    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" align="center"><input id="submit" type="Submit" name="Go" value="Do the scrubing!" class="btn" /></td>
    </tr>
  </table>
</form>

<? if(!empty($rows_count)): ?>
	<fieldset>
	  <legend>Import statiscics:</legend>
	  <ul style="text-align: left">
	  	<? foreach($rows_count as $caption => $number):?>
	  		<li><b><?=$caption?>:</b> <?=$number?></li>
	  	<? endforeach;?>
		<?
		    $url = '/csv/?page=download&max_price='
			    . $max_price
			    . '&wireless='
			    . $wireless
		    	    . '&landline='
			    . $landline;
    		    if(!empty($areacodes)) {
        		foreach($areacodes as $code) {
        		    $url .= '&areacode[]=' . $code;
        		}
    		    }
		?>
	  	<li>You can download <a href="<?=$url?>">the CSV file</a></li>
	  	<li>You can download <a href="<?=$url?>&zip=1">the zipped file</a></li>
	  </ul>
	</fieldset>
<? endif; ?>

<div style="display: none; text-align: center;" id="loader">
  <img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="loader" />
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