<?php
    $tax_status=$con->myQuery("SELECT id,code FROM tax_status WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
	Alert();
?>
<script type="text/javascript">
    function isNumberKey(evt, element) {

      var charCode = (evt.which) ? evt.which : event.keyCode
       //alert(charCode);
      if ((charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 8)) && charCode !== 45)
        return false;
      else {
        
      }
      return true;
    } 
    

</script>

<form class='form-horizontal' action='save_personal_information.php' method="POST" enctype="multipart/form-data">
	<input type='hidden' name='id' value='<?php echo !empty($employee)?$employee['id']:''; ?>'>
	<div class="form-group">  
      <div class="col-sm-12 text-center col-md-2 col-md-offset-5">
      	<?php
            if(!empty($employee))
            {
            	if(!empty($employee['image']))
                {
            		$image="employee_images/".$employee['image'];
            	}else
                {
    	            if($employee['gender']=='Male')
                    {
    	              $image="dist/img/user_male.png";
    	            }else
                    {
    	              $image="dist/img/user_female.png";
    	            }
            	}
            }else
            {
              $image="dist/img/user_placeholder.png";
            }
        ?>

      	<img src="<?php echo $image;?>" class="user-image" alt="User Image" style='width:140px;'>
        <input type="file" id="image"  name='image' accept='image/*' class="filestyle" data-classButton="btn btn-primary" data-input="false" data-classIcon="icon-plus" data-buttonText=" &nbsp;Change Image">
      </div>
    </div>
	<div class="form-group">
      <label for="code" class="col-md-3 control-label">Employee Code *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="code" placeholder="Employee Code" name='code' value='<?php echo !empty($employee)?htmlspecialchars($employee['code']):''; ?>' required>
      </div>
    </div>
    <div class="form-group">
      <label for="first_name" class="col-md-3 control-label">First Name *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="first_name" placeholder="First Name" name='first_name' value='<?php echo !empty($employee)?htmlspecialchars($employee['first_name']):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="middle_name" class="col-md-3 control-label">Middle Name </label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="middle_name" placeholder="Middle Name" name='middle_name' value='<?php echo !empty($employee)?htmlspecialchars($employee['middle_name']):''; ?>'>
      </div>
    </div>
    <div class="form-group">
      <label for="last_name" class="col-md-3 control-label">Last Name *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="last_name" placeholder="Last Name" name='last_name' value='<?php echo !empty($employee)?htmlspecialchars($employee['last_name']):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="nationality" class="col-md-3 control-label">Nationality *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="nationality" placeholder="Nationality" name='nationality' value='<?php echo !empty($employee)?htmlspecialchars($employee['nationality']):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="birthday" class="col-md-3 control-label">Date of Birth *</label>
      <div class="col-md-7">
        <input type="text" class="form-control date_picker" id="birthday" name='birthday' value='<?php echo !empty($employee)?htmlspecialchars(DisplayDate($employee['birthday'])):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="gender" class="col-md-3 control-label">Gender *</label>
      <div class="col-md-7">
      	<select name='gender' class='form-control cbo'  required>
      		<option value='' disabled="disabled" <?php echo empty($employee)?'selected="selected"':''; ?>>Select Gender</option>
      		<option value='Male' <?php echo !empty($employee) && $employee['gender']=='Male'?'selected="selected"':''; ?>>Male</option>
      		<option value='Female' <?php echo !empty($employee) && $employee['gender']=='Female'?'selected="selected"':''; ?>>Female</option>
      	</select>
      </div>
    </div>
    <div class="form-group">
      <label for="civil_status" class="col-md-3 control-label">Civil Status *</label>
      <div class="col-md-7">
      	<select name='civil_status' class='form-control'  required>
      		<option value='' disabled="disabled" <?php echo empty($employee)?'selected="selected"':''; ?>>Select Civil Status</option>
      		<?php 
      			foreach (array('Single','Married','Divorced','Widowed') as $value):
      		?>
          		<option value='<?php echo $value?>' <?php echo !empty($employee) && $employee['civil_status']==$value?'selected="selected"':''; ?>><?php echo $value?></option>
      		<?php
      			endforeach;
      		?>
      	</select>
      </div>
    </div>
    <div class="form-group">
      <label for="sss_no" class="col-md-3 control-label">SSS Number </label>
      <div class="col-md-7">
        <input type="text" pattern="[0-9]{2}-[0-9]{7}-[0-9]{2}"  class='form-control sss' id="sss_no" placeholder="SSS Number" name='sss_no' value='<?php echo !empty($employee)?htmlspecialchars($employee['sss_no']):''; ?>'>
          <label for="w_sss" class="col-md-12 ">
            <input type="checkbox" class="" id="w_sss" name='w_sss' <?php echo !empty($employee) && !empty($employee['w_sss'])?'checked="true"':''; ?> title='Deduct sss from payroll.' value='1'>
            Deduct SSS from payroll
          </label>
      </div>
    </div>
    <div class="form-group">
      <label for="tin" class="col-md-3 control-label">Tax Identification Number </label>
      <div class="col-md-7">
        <input type="text" pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{3}" class='form-control tin' id="tin" placeholder="Tax Identification Number" name='tin' value='<?php echo !empty($employee)?htmlspecialchars($employee['tin']):''; ?>'>
      </div>
    </div>
    <div class="form-group">
      <label for="philhealth" class="col-md-3 control-label">Philhealth </label>
      <div class="col-md-7">
        <input type="text" pattern="[0-9]{2}-[0-9]{9}-[0-9]{1}" class='form-control philhealth' id="philhealth" placeholder="Philhealth" name='philhealth' value='<?php echo !empty($employee)?htmlspecialchars($employee['philhealth']):''; ?>'>
          <label for="w_philhealth" class="col-md-12">
            <input type="checkbox" class="" id="w_philhealth"  name='w_philhealth' <?php echo !empty($employee) && !empty($employee['w_philhealth'])?'checked="true"':''; ?> title='Deduct philhealth from payroll.' value='1'>
            Deduct Philhealth from payroll
          </label>
      </div>
    </div>
    <div class="form-group">
      <label for="pagibig" class="col-md-3 control-label">Pagibig </label>
      <div class="col-md-7">
        <input type="text" pattern="[0-9]{4}-[0-9]{4}-[0-9]{4}" class='form-control pagibig' id="pagibig" placeholder="Pagibig" name='pagibig' value='<?php echo !empty($employee)?htmlspecialchars($employee['pagibig']):''; ?>'>
          <label for="w_hdmf" class="col-md-12">
            <input type="checkbox" class="" id="w_hdmf"  name='w_hdmf' <?php echo !empty($employee) && !empty($employee['w_hdmf'])?'checked="true"':''; ?> title='Deduct pagibig from payroll.' value='1'>
            Deduct Pagibig from payroll
          </label>
      </div>
    </div>

    
    <div class="form-group">
      <label for="address1" class="col-md-3 control-label">Address 1 *</label>
      <div class="col-md-7">
      	<textarea class='form-control' name='address1' id='address1'  required><?php echo !empty($employee)?htmlspecialchars($employee['address1']):''; ?></textarea>
      </div>
    </div>
    <div class="form-group">
      <label for="address2" class="col-md-3 control-label">Address 2 </label>
      <div class="col-md-7">
      	<textarea class='form-control' name='address2' id='address2' ><?php echo !empty($employee)?htmlspecialchars($employee['address2']):''; ?></textarea>
      </div>
    </div>
    <div class="form-group">
      <label for="city" class="col-md-3 control-label">City *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="city" placeholder="City" name='city' value='<?php echo !empty($employee)?htmlspecialchars($employee['city']):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="province" class="col-md-3 control-label">Province *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="province" placeholder="Province" name='province' value='<?php echo !empty($employee)?htmlspecialchars($employee['province']):''; ?>' required>
      </div>
    </div>
    <div class="form-group">
      <label for="country" class="col-md-3 control-label">Country *</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="country" placeholder="Country" name='country' value='<?php echo !empty($employee)?htmlspecialchars($employee['country']):''; ?>'  required>
      </div>
    </div>
    <div class="form-group">
      <label for="postal_code" class="col-md-3 control-label">Postal Code </label>
      <div class="col-md-7">
        <input type="text" pattern="[0-9]{4}" class='form-control zip' id="postal_code" placeholder="Postal Code" name='postal_code' value='<?php echo !empty($employee)?htmlspecialchars($employee['postal_code']):''; ?>' >
      </div>
    </div>
    <div class="form-group">
      <label for="contact_no" class="col-md-3 control-label">Contact No *</label>
      <div class="col-md-7">
        <input type="number" class="form-control" id="contact_no" placeholder="Contact No" name='contact_no' value='<?php echo !empty($employee)?htmlspecialchars($employee['contact_no']):''; ?>' required>
      </div>
    </div>
    <div class="form-group">
      <label for="work_contact_no" class="col-md-3 control-label">Work Contact No </label>
      <div class="col-md-7">
        <input type="number" class="form-control" id="work_contact_no" placeholder="Work Contact No" name='work_contact_no' value='<?php echo !empty($employee)?htmlspecialchars($employee['work_contact_no']):''; ?>' >
      </div>
    </div>
    <div class="form-group">
      <label for="private_email" class="col-md-3 control-label">Email Address *</label>
      <div class="col-md-7">
        <input type="email" class="form-control" id="private_email" placeholder="Email Address" name='private_email' value='<?php echo !empty($employee)?htmlspecialchars($employee['private_email']):''; ?>' required>
      </div>
    </div>
    <div class="form-group">
      <label for="work_email" class="col-md-3 control-label">Work Email Address </label>
      <div class="col-md-7">
        <input type="email" class="form-control" id="work_email" placeholder="Email Address" name='work_email' value='<?php echo !empty($employee)?htmlspecialchars($employee['work_email']):''; ?>'>
      </div>
    </div>
    

    
    
    <div class="form-group">
      <label for="tax_status_id" class="col-md-3 control-label">Tax Status *</label>
      <div class="col-md-7">
      	<select name='tax_status_id' class='form-control cbo' data-placeholder="Select Tax Status " <?php echo !(empty($employee))?"data-selected='".$employee['tax_status_id']."'":NULL ?> style='width:100%' required>
      		<?php
      			echo makeOptions($tax_status);
      		?>
      	</select>
      </div>
    </div>
    <div class="form-group">
      <label for="acu_id" class="col-md-3 control-label">Access Unit ID  </label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="acu_id"  name='acu_id' placeholder="Access Unit ID" value='<?php echo !empty($employee)?htmlspecialchars($employee['acu_id']):''; ?>'>
      </div>
    </div>
        <div class="form-group">
      <div class="col-sm-10 col-md-offset-2 text-center">
      	<a href='employees.php' class='btn btn-default'>Back to Employees</a>
        <button type='submit' class='btn btn-warning'>Save </button>
      </div>
    </div>
</form>