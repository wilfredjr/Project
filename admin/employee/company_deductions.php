<?php
  $data=$con->myQuery("SELECT ecd.id,ecd.emp_code,ecd.comde_code,ecd.emp_comde_amt,ecd.emp_comde_start_date,ecd.emp_comde_end_date,ecd.emp_deduct_type,cd.comde_desc FROM employee_company_deductions ecd JOIN company_deductions cd ON cd.comde_code=ecd.comde_code WHERE ecd.is_deleted=0 AND ecd.emp_id=?", array($employee['id']))->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($_GET['ec_id'])) {
    $record=$con->myQuery("SELECT id,emp_code,comde_code,emp_comde_amt as amount,emp_comde_start_date as start_date,emp_comde_end_date as end_date,emp_deduct_type FROM employee_company_deductions WHERE emp_id=? AND id=? LIMIT 1", array($employee['id'],$_GET['ec_id']))->fetch(PDO::FETCH_ASSOC);
  }
  $company_deductions=$con->myQuery("SELECT comde_code as id,comde_desc FROM company_deductions WHERE is_deleted=0 AND is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $deduction_types=$con->myQuery("SELECT id,name FROM deduction_types WHERE is_deleted=0 AND is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $tab=12;
?>
<?php
  $has_error=FALSE;
  if(!empty($_SESSION[WEBAPP]['Alert']) && $_SESSION[WEBAPP]['Alert']['Type']=="danger"){
    $has_error=TRUE;
  }
  Alert();
?>
<div class='text-right'>
<button class='btn btn-warning' data-toggle="collapse" data-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">Toggle Form </button>
</div>
<br/>
<div id='collapseForm' class='collapse'>
  <form class='form-horizontal' action='save_employee_company_deductions.php' method="POST" >
    <input type='hidden' name='employee_id' value='<?php echo !empty($employee)?$employee['id']:''; ?>'>
    <input type='hidden' name='id' value='<?php echo !empty($record)?$record['id']:''; ?>'>
      <div class="form-group">
        <label for="company_deduction_id" class="col-md-3 control-label">Company Deductions *</label>
        <div class="col-md-7">
          <select name='company_deduction_id' class='form-control cbo' data-placeholder="Select Company Deductions" <?php echo !(empty($record))?"data-selected='".$record['comde_code']."'":NULL ?> style='width:100%' required>
            <?php
              echo makeOptions($company_deductions);
            ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="deductinp_type_id" class="col-md-3 control-label">Deduction Type *</label>
        <div class="col-md-7">
          <select name='deduction_type_id' class='form-control cbo' data-placeholder="Select Deduction Type" <?php echo !(empty($record))?"data-selected='".$record['emp_deduct_type']."'":NULL ?> style='width:100%' required>
            <?php
              echo makeOptions($deduction_types);
            ?>
          </select>
        </div>
      </div>
      <div class="form-group">
          <label for="start_date" class="col-md-3 control-label">Start Date *</label>
          <div class="col-md-7">
            <input type="text" class="form-control date_picker" id="start_date"  name='start_date' value='<?php echo !empty($record)?htmlspecialchars(DisplayDate($record['start_date'])):''; ?>' required>
          </div>
      </div>
      <div class="form-group">
          <label for="end_date" class="col-md-3 control-label">End Date *</label>
          <div class="col-md-7">
            <input type="text" class="form-control date_picker" id="end_date"  name='end_date' value='<?php echo !empty($record)?htmlspecialchars(DisplayDate($record['end_date'])):''; ?>' required>
          </div>
      </div>
      <div class="form-group">
          <label for="amount" class="col-md-3 control-label">Amount *</label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="amount" placeholder="Enter Amount" name='amount' value='<?php echo !empty($record)?htmlspecialchars($record['amount']):''; ?>' required>
          </div>
      </div>

      <div class="form-group">
        <div class="col-sm-10 col-md-offset-2 text-center">
          <a href='frm_employee.php?id=<?php echo $employee['id']?>&tab=<?php echo $tab?>' class='btn btn-default' onclick="return confirm('<?php echo empty($record)?"Cancel creation of new company deduction?":"Candel modification of company deduction?" ?>')">Cancel</a>
          <button type='submit' class='btn btn-warning'>Save </button>
        </div>
      </div>
  </form>
</div>
<br/>
<table id='ResultTable' class='table table-bordered table-striped'>
  <thead>
    <tr>
      <th class='text-center'>Deduction Code</th>
      <th class='text-center'>Deduction Description</th>
      <th class='text-center'>Amount</th>
      <th class='text-center'>Start Date</th>
      <th class='text-center'>End Date</th>
      <th class='text-center'>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($data as $row):
    ?>
      <tr>
        <td class='text-center'><?php echo htmlspecialchars($row['comde_code'])?></td>
        <td class='text-center'><?php echo htmlspecialchars($row['comde_desc'])?></td>
        <td class='text-center'><?php echo htmlspecialchars(number_format($row['emp_comde_amt'],2))?></td>
        <td class='text-center'><?php echo htmlspecialchars(date_format(date_create($row['emp_comde_start_date']), DATE_FORMAT_PHP))?></td>
        <td class='text-center'><?php echo htmlspecialchars(date_format(date_create($row['emp_comde_end_date']), DATE_FORMAT_PHP))?></td>
        <td class='text-center'>
          <a href='frm_employee.php?id=<?php echo $employee['id']?>&tab=<?php echo $tab?>&ec_id=<?php echo $row['id']?>' class='btn btn-success btn-sm'><span class='fa fa-pencil'></span></a>
          <form style="display: inline" method="POST" action="delete_employee_company_deductions.php" onsubmit="return confirm('This record will be deleted.')">
            <input type="hidden" name="employee_id" value="<?php echo $employee['id']?>">
            <input type="hidden" name="id" value="<?php echo $row['id']?>">
            <button type="submit" class='btn btn-danger btn-sm'><span class='fa fa-trash'></span></button>
          </form>
        </td>
      </tr>
    <?php
      endforeach;
    ?>
  </tbody>
</table>
<?php 
  if($has_error===TRUE || !empty($record)):
?>
<script type="text/javascript">
  $(function(){
    $('#collapseForm').collapse({
      toggle: true
    })    
  });
</script>

<?php
  endif;
?>