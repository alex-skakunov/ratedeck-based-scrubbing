<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>

<div style="text-align: center; margin-bottom: 50px;">
	<h1>
  		Upload the DNC list
	</h1>
</div>

<form action="index.php?page=blacklist" method="post" enctype="multipart/form-data" onsubmit="$('#submit, #truncate').attr('disabled', 'disabled'); $('#loader').show();">
   <input type="hidden" name="version" value="1.0" />
   <table border="0" width="50%" align="center">
    <tr>
      <td align="right"><label for="number"><b>DNC file:</b></label></td>
      <td width="20px">&nbsp;</td>
      <td style="padding-bottom: 30px; text-align: left; margin-left: 30px; vertical-align: middle">
          <input type="file" name="file_source" id="file_source" class="edt" accept=".csv, .txt, .zip, application/zip, text/csv, text/plain"/>
      </td>
    </tr>
    <tr>
      <td colspan="2" ></td>
      <td align="left">
        <small>
         Upload a CSV in any of the following formats of each line:
         <ul>
            <li>Full number</li>
            <li>Area code, Number</li>
         </ul>
        </small>
        <hr/>

      </td>
    </tr>

    <tr>
      <td align="right"><b>Type:</b></td>
      <td rowspan="2" width="20px">&nbsp;</td>
      <td style="text-align: left; margin-left: 30px;">
          <? foreach($blacklistsList as $token) : ?>
              <label for="<?=$token?>_dnc">
                  <input type="radio" name="blacklist_type" id="<?=$token?>_dnc" value="<?=$token?>" /> <?=ucfirst($token)?> DNC
                  <small class="text-muted"> (Count: <span id="<?=$token?>_count">Loading...</span>)</small>
              </label><br/>
              <script>
                  $.getJSON('index.php?page=blacklist-admin-count&name=<?=$token?>', function(data) {$('#<?=$token?>_count').html('approx. ' + data.count);});
              </script>
          <? endforeach; ?>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" align="center">
        <input id="submit" type="Submit" name="Go" value="Add the numbers to blacklist!" class="btn btn-primary" />
      </td>
    </tr>
  </table>
</form>


<p class="text-muted" style="text-align: right">
    <? foreach($blacklistsList as $token) : ?>
        <small><a href="?page=blacklist&truncate=<?=$token?>" onclick="return confirm('Are you sure you want to erase the <?=ucfirst($token)?> DNC list?')">Click here to erase the <?=ucfirst($token)?> DNC list</a></small><br/>
    <? endforeach; ?>
</p>

<? if(!empty($rows_count)): ?>
  <h2 align="center">File is imported! Now your blacklist contains <?=$rows_count?> records.</h2>
<? endif; ?>

<div style="display: none; text-align: center;" id="loader">
  <img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="loader" /><br/>
  For very big files, this page might hang. You will get an email when processing of this file would have been finished
</div>
