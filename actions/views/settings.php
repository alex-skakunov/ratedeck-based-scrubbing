<div style="text-align: center; margin-bottom: 50px;">
  <h1>
    Settings
  </h1>
</div>

<form method="post" enctype="multipart/form-data" onsubmit="$('#loader').show();">
   <table border="0" align="center">
    <tr>
      <td><label for="user_password">Change the password:</label></td>
      <td width="10px">&nbsp;</td>
      <td><input type="password" name="user_password" id="user_password" class="edt" value="<?=$user_password?>" /></td>
      <td>&nbsp;</td>
      <td><input id="user_submit" type="Submit" name="user_submit" value="Save" class="btn btn-primary" style="padding: 3px 15px" /></td>
    </tr>
    <tr>
      <td colspan="5">&nbsp;</td>
    </tr>
  </table>
</form>

