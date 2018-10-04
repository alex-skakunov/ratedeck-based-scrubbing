
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>

<div style="text-align: center; margin-bottom: 50px;">
  <h1>
    Upload the fresh ratedeck file
  </h1>
</div>

<form method="post" enctype="multipart/form-data" onsubmit="$('#submit').attr('disabled', 'diabled'); $('#loader').show();">
   <input type="hidden" name="version" value="1.0" />
   <table border="0" align="center">
    <tr>
      <td>Ratedeck CSV file to import:</td>
      <td rowspan="30" width="10px">&nbsp;</td>
      <td><input type="file" name="file_source" id="file_source" class="edt" value="<?=$file_source?>" " accept=".csv, .txt, .zip, application/zip, text/csv, text/plain" /></td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" align="center"><input id="submit" type="Submit" name="Go" value="Upload the ratedeck" class="btn" onclick="var s = document.getElementById('file_source'); if(null != s && '' == s.value) {alert('Define file name'); s.focus(); return false;} var s = document.getElementById('table'); if(null != s && 0 == s.selectedIndex) {alert('Define table name'); s.focus(); return false;}"></td>
    </tr>
  </table>
</form>

<? if(!empty($rows_count)): ?>
  <h2 align="center">Imported <?=(int)$rows_count?> records</h2>
<? endif; ?>

<div style="display: none; text-align: center;" id="loader">
  <img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="loader" />
</div>
