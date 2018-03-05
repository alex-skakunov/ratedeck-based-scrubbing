<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>

<div style="text-align: center; margin-bottom: 50px;">
	<h1>
  		Update the blacklist
	</h1>
</div>

<form method="post" enctype="multipart/form-data" onsubmit="$('#submit, #truncate').attr('disabled', 'disabled'); $('#loader').show();">
   <input type="hidden" name="version" value="1.0" />
   <table border="0" width="50%" align="center">
    <tr>
      <td align="right"><label for="number">Blacklist file to append:</label></td>
      <td rowspan="2" width="20px">&nbsp;</td>
      <td>
          <input type="file" name="file_source" id="file_source" class="edt" value="<?=$file_source?>" accept=".csv, .txt, .zip, application/zip, text/csv, text/plain"/>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" align="center"><input id="submit" type="Submit" name="Go" value="Add the numbers to blacklist!" class="btn" /></td>
    </tr>
  </table>
</form>

<form method="post" enctype="multipart/form-data" style="margin-top: 100px"
    onsubmit="$('#submit').attr('disabled', 'disabled'); $('#loader').show();">
   <input type="hidden" name="version" value="1.0" />
   <table border="0" width="50%" align="center">
    <tr>
      <td align="right">
        <input id="truncate" type="Submit" name="truncate" value="Erase all data in blacklist" class="btn" style="font-size: 14px; color: red;" onclick="return confirm('Do you really want to permanently remove all records in the blacklist?')"/>
      </td>
    </tr>
  </table>
</form>

<? if(!empty($rows_count)): ?>
  <h2 align="center">File is imported! Now your blacklist contains <?=$rows_count?> records.</h2>
<? endif; ?>


<? if(!empty($message)): ?>
  <h2 align="center"><?=$message?></h2>
<? endif; ?>

<div style="display: none; text-align: center;" id="loader">
  <img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="loader" />
</div>
