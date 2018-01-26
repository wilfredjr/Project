<?php
  $data=$con->myQuery("SELECT id,rta_code as dmb_code,rta_desc as dmb_desc,rta_amount as dmb_amount,rta_taxable,rta_type FROM receivable_and_taxable_allowances WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $current_de_minimis=$con->myQuery("SELECT rta_code as dmb_code FROM employee_receivable_and_taxable_allowances WHERE emp_id=?", array($employee['id']))->fetchAll(PDO::FETCH_COLUMN);
  $tab=10;
?>
<?php
  $has_error=false;
if (!empty($_SESSION[WEBAPP]['Alert']) && $_SESSION[WEBAPP]['Alert']['Type']=="danger") {
      $has_error=true;
}
  Alert();
?>
<br/>
<form method="POST" action='save_employee_receivable_taxable_allowances.php'>
<input type='hidden' name='employee_id' value='<?php echo !empty($employee)?$employee['id']:''; ?>'>
<input type='hidden' name='emp_code' value='<?php echo !empty($employee)?$employee['code']:''; ?>'>
<div class='text-right'>
  <button type='submit' class='btn btn-warning'><span class='fa fa-save'></span> Save </button>
  <button type='button' class='btn btn-default' onclick="clear_selected()"> Clear </button>
</div>
<br/><br/>
<table id='' class='table table-bordered table-striped table-condensed'>
  <thead>
    <tr>
      <th class='text-center' style="max-width: 20px;width: 20px"></th>
      <th class='text-center'>Code</th>
      <th class='text-center'>Description</th>
      <th class='text-center'>Amount</th>
      <th class='text-center'>Taxable</th>
      <th class='text-center'>Type</th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach ($data as $row) :
    ?>
      <tr>
        <td class='text-center'><input type='checkbox' name='dmb_code[]' value='<?php echo htmlspecialchars($row['dmb_code'])?>' <?php echo in_array($row['dmb_code'], $current_de_minimis)?"checked":"" ?>></td>
        <td class='text-center'><?php echo htmlspecialchars($row['dmb_code'])?></td>
        <td class='text-center'><?php echo htmlspecialchars($row['dmb_desc'])?></td>
        <td class='text-center'><?php echo htmlspecialchars(number_format($row['dmb_amount'], 2))?></td>
        <td class='text-center'><?php echo htmlspecialchars($row['rta_taxable']=="NO"?"Non-taxable":"Taxable")?></td>
        <td class='text-center'><?php echo htmlspecialchars($row['rta_type'])?></td>
      </tr>
    <?php
    endforeach;
    ?>
  </tbody>
</table>
</form>
<script type="text/javascript">
  function clear_selected() {
    $("input[name='dmb_code[]']").removeAttr('checked');
  }
</script>