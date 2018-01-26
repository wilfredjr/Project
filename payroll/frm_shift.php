<?php
	require_once("../support/config.php");

	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_GET['id']))
	{
		// $get_shift_details=$con->myQuery("SELECT
		// 	shift_name,
		// 	time_in,
		// 	time_out,
		// 	beginning_time_in,
		// 	beginning_time_out,
		// 	ending_time_in,
		// 	ending_time_out,
		// 	break_one_start,
		// 	break_one_end,
		// 	break_two_start,
		// 	break_two_end,
		// 	break_three_start,
		// 	break_three_end,
		// 	working_days,
		// 	late_start,
		// 	grace_minutes
		// 	FROM
		// 	shifts
		// 	WHERE
		// 	is_deleted = 0 and id = ?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

		$get_shift_details=$con->myQuery("SELECT
			shift_name,
			time_in,
			time_out,
			working_days,
			late_start,
			grace_minutes
			FROM
			shifts
			WHERE
			is_deleted = 0 and id = ?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

	}

	makeHead("Shifts",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Shifts</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
			<?php
			Alert();
			Modal();

			?>

			<div class="col-lg-12 col-md-12">
				<form method="post" action="../payroll/save_shift.php" class="form-horizontal">
					<div class='form-group'>
						<div class='col-md-3'>
						</div>
						<div class='col-md-3'>
							<input type="hidden" name="thatday" value="<?php echo !empty($get_shift_details['working_days'])?htmlspecialchars($get_shift_details['working_days']):''?>">
							<input type='hidden' name='shift_id' class='form-control' id='shift_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>">
						</div>
					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Shift Name *</label>
						<div class='col-md-3'>
							<input type='text' name='shift_name' class='form-control' id='shift_name' value="<?php echo !empty($get_shift_details['shift_name'])?htmlspecialchars($get_shift_details['shift_name']):''?>" required>
						</div>
					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Time In *</label>
						<div class='col-md-3'>
							<input type='time' name='time_in' class='form-control' id='time_in' value="<?php echo !empty($get_shift_details['time_in'])?htmlspecialchars($get_shift_details['time_in']):''?>" required>
						</div>
						<label class='col-md-2 text-right' >Time Out *</label>
						<div class='col-md-3'>
							<input type='time' name='time_out' class='form-control' id='time_out' value="<?php echo !empty($get_shift_details['time_out'])?htmlspecialchars($get_shift_details['time_out']):''?>" required>
						</div>

					</div>
					<div class='form-group'>
			            <label class='col-md-3 text-right' >Late Start *</label>
						<div class='col-md-3'>
							<input type='time' name='late_start' class='form-control' id='late_start' value="<?php echo !empty($get_shift_details['late_start'])?htmlspecialchars($get_shift_details['late_start']):''?>" required>
						</div>
			      		<label class='col-md-2 text-right' >Grace Period *</label>
						<div class='col-md-3'>
							<input type='text' name='grace_minutes' class='form-control numeric' id='grace_minutes' value="<?php echo !empty($get_shift_details['grace_minutes'])?htmlspecialchars($get_shift_details['grace_minutes']):''?>" maxlength="2" required>
						</div>
				    </div>
					<!-- <div class='form-group'>
						<label class='col-md-3 text-right' >Beginning Time In *</label>
						<div class='col-md-3'>
							<input type='time' name='beg_time_in' class='form-control' id='beg_time_in' value="<?php //echo !empty($get_shift_details['beginning_time_in'])?htmlspecialchars($get_shift_details['beginning_time_in']):''?>" required>
						</div>
						<label class='col-md-2 text-right' >Beginning Time Out *</label>
						<div class='col-md-3'>
							<input type='time' name='beg_time_out' class='form-control' id='beg_time_out' value="<?php //echo !empty($get_shift_details['beginning_time_out'])?htmlspecialchars($get_shift_details['beginning_time_out']):''?>" required>
						</div>

					</div>
					<div class='form-group'>
						<label class='col-md-3 text-right' >Ending Time In *</label>
						<div class='col-md-3'>
							<input type='time' name='end_time_in' class='form-control' id='end_time_in' value="<?php //echo !empty($get_shift_details['ending_time_in'])?htmlspecialchars($get_shift_details['ending_time_in']):''?>" required>
						</div>
						<label class='col-md-2 text-right' >Ending Time Out *</label>
						<div class='col-md-3'>
							<input type='time' name='end_time_out' class='form-control' id='end_time_out' value="<?php //echo !empty($get_shift_details['ending_time_out'])?htmlspecialchars($get_shift_details['ending_time_out']):''?>" required>
						</div>

					</div>
					<div class='form-group'>
						<label class='col-md-3 text-right' >Break One Time In </label>
						<div class='col-md-3'>
							<input type='time' name='brkone_time_in' class='form-control' id='brkone_time_in' value="<?php //echo !empty($get_shift_details['break_one_start'])?htmlspecialchars($get_shift_details['break_one_start']):''?>" >
						</div>
						<label class='col-md-2 text-right' >Break One Time Out </label>
						<div class='col-md-3'>
							<input type='time' name='brkone_time_out' class='form-control' id='brkone_time_out' value="<?php //echo !empty($get_shift_details['break_one_end'])?htmlspecialchars($get_shift_details['break_one_end']):''?>" >
						</div>

					</div>
					<div class='form-group'>
						<label class='col-md-3 text-right' >Break Two Time In </label>
						<div class='col-md-3'>
							<input type='time' name='brktwo_time_in' class='form-control' id='brktwo_time_in' value="<?php //echo !empty($get_shift_details['break_two_start'])?htmlspecialchars($get_shift_details['break_two_start']):''?>" >
						</div>
						<label class='col-md-2 text-right' >Break Two Out </label>
						<div class='col-md-3'>
							<input type='time' name='brktwo_time_out' class='form-control' id='brktwo_time_out' value="<?php //echo !empty($get_shift_details['break_two_end'])?htmlspecialchars($get_shift_details['break_two_end']):''?>" >
						</div>

					</div>
					<div class='form-group'>
						<label class='col-md-3 text-right' >Break Three Time In </label>
						<div class='col-md-3'>
							<input type='time' name='brkthree_time_in' class='form-control' id='brkthree_time_in' value="<?php //echo !empty($get_shift_details['break_three_start'])?htmlspecialchars($get_shift_details['break_three_start']):''?>" >
						</div>
						<label class='col-md-2 text-right' >Break Three Out </label>
						<div class='col-md-3'>
							<input type='time' name='brkthree_time_out' class='form-control' id='brkthree_time_out' value="<?php //echo !empty($get_shift_details['break_three_end'])?htmlspecialchars($get_shift_details['break_three_end']):''?>" >
						</div>

					</div> -->
					<div class='form-group'>
						<label class='control-label col-md-3 text-right'>Working Day(s) *</label>
						<div class='col-md-3'>
							<select id='days' class="form-control cbo text-blue" name="days[]" multiple="multiple" data-placeholder="Select a day" style="width: 100%;" required>
								<option value='M'>Monday</option>
								<option value='T'>Tuesday</option>
								<option value='W'>Wednesday</option>
								<option value='TH'>Thursday</option>
								<option value='F'>Friday</option>
								<option value='Sa'>Saturday</option>
								<option value='Su'>Sunday</option>
							</select>
						</div>			
					</div>
					<div class="form-group">
						<div class="col-sm-9 col-md-offset-2 text-center">
							<a href='view_shift.php' class='btn btn-default' onclick="return confirm('<?php echo empty($data)?"Cancel creation of shift?":"Cancel modification of shift?" ?>')">Cancel</a>
							<button type='submit' class='btn btn-danger'>Save </button>
						</div>
					</div>

				</form>
			</div>
		</div>
		<br/>
	</section>
</div>

<script type="text/javascript">
	shift_id = $("input[name='thatday']").val();

	if (shift_id != '')
	{	
		var arr = new Array();
		var thatday=$("input[name='thatday']").val();
		arr = thatday.split(",");
		$("#days").val(arr).change();
	}

	function reset()
	{
		if(confirm("Are you sure you want to clear all fields?"))
		{
			$("input").val("");
		}
	}

</script>
<?php
	makeFoot(WEBAPP,1)
?>