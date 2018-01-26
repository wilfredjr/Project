<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
    toLogin();
    die();
}

if (empty($_GET['id'])) {
    Alert("Invalid Record Selected.", "danger");
    die;
} else {
    $loan_information=$con->myQuery("SELECT
    code as employee_code,
    emp_loan_id,
    cut_off_no,
    loan_amount,
    balance,
    status_name as status,
    loan_status.status_id,
    loans.loan_id,
    loans.loan_name,
    employee_id,
    CONCAT(last_name,', ',first_name,' ',middle_name) AS employee_name,
    employees.contact_no,
    employees.work_contact_no,
    employees.work_email,
    employees.private_email
    FROM
    emp_loans
    JOIN loans ON loans.loan_id = emp_loans.loan_id
    JOIN employees ON employees.id=emp_loans.employee_id
    JOIN loan_status ON loan_status.status_id=emp_loans.status_id
    WHERE emp_loan_id=? LIMIT 1", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

    if (empty($loan_information)) {
        Alert("Invalid Record Selected.", "danger");
        die;
    }
}



makeHead("Loan Payment History", 1);
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section>
        <div class="content-header">
            <h1 class="page-header text-center text-red">Loan Payment History</h1>
        </div>
    </section>



    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            Alert();
            Modal();
            ?>
            <div class='col-xs-12'>
                <div class="col-md-12">
                    <a href="view_loan.php" class='btn btn-danger btn-flat'><span class='fa fa-arrow-left'></span> Loans</a>
                </div>
                <div class="col-md-12">
                    <h3>
                        Employee Information
                    </h3>
                    <div class="page-header"></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Employee Code:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['employee_code'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Employee:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['employee_name'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Contact No:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['contact_no'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Work Contact No:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['work_contact_no'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Private Email:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['private_email'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Work Email:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['work_email'])?></div>
                </div>

                <div class="col-md-12">
                    <h3>
                        Loan Information
                    </h3>
                    <div class="page-header"></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Status:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['status'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Loan Type:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['loan_name'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Cut Off No.:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars($loan_information['cut_off_no'])?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Loan Amount:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars(number_format($loan_information['loan_amount'], 2))?></div>
                </div>
                <div class="col-md-12">
                    <div class="col-xs-12 col-sm-3"><strong>Balance:</strong></div>
                    <div class="col-xs-12 col-sm-9"><?php echo htmlspecialchars(number_format($loan_information['balance'], 2))?></div>
                </div>
            </div>
            <div class='col-sm-12'>
                
            
            <div class="col-lg-12 col-md-12">
            <br/>
                <form method="post" action="" class="form-horizontal">
                    <div class='form-group'>
                      <label class='col-md-3 text-right' >Start Date: </label>
                      <div class='col-md-3'>
                        <input type='text' name='date_start' class='form-control date_picker' id='date_start' pattern="\d{1,2}/\d{1,2}/\d{4}" value=''>
                      </div>
                      <label class='col-md-3 text-right' >End Date: </label>
                      <div class='col-md-3'>
                        <input type='text' name='date_end' class='form-control date_picker' id='date_end' pattern="\d{1,2}/\d{1,2}/\d{4}" value=''>
                      </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-md-3 text-right' >Action Type :</label>
                        <div class='col-md-3'>
                            <select name="action_type" class="form-control cbo" data-placeholder="Filter by Action Type" >
                                <option value=""></option>
                                <option value="Passed">Passed</option>
                                <option value="Deduct">Deduct</option>
                            </select>
                        </div>
                    </div>                    
                    <div class='form-group'>
                        <div class='col-md-7 text-right'>
                            <button type='button'  class='btn-flat btn btn-danger' onclick="filter_search()">Filter &nbsp;<span class="fa fa-search"></span></button>
                            <a   class='btn-flat btn btn-default' onclick="reset()" >Clear</a>
                        </div>
                    </div>

                </form>
            </div>
            </div>
        </div>

        <br/>
        

        <div class='panel panel-default'>
            <div class='panel-body ' >
            <?php
                if(in_array($loan_information['status_id'], array("1","2"))):
            ?>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class='btn btn-danger btn-flat' onclick="$('#collapseForm').collapse('show');"  aria-expanded="false" aria-controls="collapseForm"> Create New Pass <span class='fa fa-plus'></span></button>
                </div>
                <div class="col-md-12">
                    <br/>
                    <div id='collapseForm' class='collapse'>
                    <div class="well">
                      <form class='form-horizontal' action='save_loan_pass.php' method="POST" >
                        <input type='hidden' name='emp_loan_id' value='<?php echo !empty($loan_information)?$loan_information['emp_loan_id']:''; ?>'>
                          <div class="form-group">
                            <label for="certification_id" class="col-md-3 control-label">Effective Date *</label>
                            <div class="col-md-7">
                                <input type='text' name='effective_date' class='form-control date_picker' id='effective_date' pattern="\d{1,2}/\d{1,2}/\d{4}" value='' required="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="certification_id" class="col-md-3 control-label">Reason *</label>
                            <div class="col-md-7"> 
                                <textarea name="reason" class='form-control' required="" style="resize:none" max="255"></textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <div class="col-sm-12 text-center">
                              <button type='submit' class='btn btn-danger btn-flat'>Save <span class="fa fa-save"></span></button>
                              <button type='button' class='btn btn-default btn-flat' onclick="clear_loan_pass()">Cancel </button>
                            </div>
                          </div>
                      </form>
                        </div>
                    </div>
                </div>
                <br/>

            </div>
            <?php
                endif;
            ?>

                <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
                    <thead>
                        <tr>
                            <th class='text-center'>Date</th>
                            <th class='text-center'>Type</th>
                            <th class='text-center'>Amount</th>
                            <th class='text-center'>Reason</th>
                        </tr>
                    </thead>
                    <tbody> 
                    </tbody>
                </table>


            </div>
        </div>

    </section>

</div>

<script type="text/javascript">
    $(function () {
        data_table=$('#ResultTable').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":{
                "url":"ajax/loan_payment_history.php",
                "data":function(d){
                            d.loan_id='<?php echo $loan_information['emp_loan_id']; ?>';
                            d.date_start=$("#date_start").val();
                            d.date_end=$("#date_end").val();
                            d.action_type=$("select[name='action_type']").val();
                          }
            },
            "oLanguage": { "sEmptyTaWble": "No History found." },
            "order":[0,"desc"]
        });
    });

    function reset(){
        if ($("#date_start").val()!="" || $("#date_end").val()!="" || $("select[name='action_type']").val()!="") {
            if(confirm("Are you sure you want to clear all fields?")){
                $("#date_start").val("");
                $("#date_end").val("");
                $("select[name='action_type']").each(function(){
                    $(this).val('').trigger('change');
                });
            }
        } else {
            $("#date_start").val("");
            $("#date_end").val("");
            $("select[name='action_type']").each(function(){
                $(this).val('').trigger('change');
            });
        }
    }

    function filter_search() 
    {
        var date_start=$("input[name='date_start']").val();
        var date_end=$("input[name='date_end']").val();

        if (date_start!=="" && date_end!=="" && (date_start > date_end)) {
            alert("Date start cannot be greater than date end.");
            return false;
        }

        data_table.ajax.reload();
    }

    function clear_loan_pass() {
        if ($("input[name='effective_date']").val()!="" || $("textarea[name='reason']").val()!="") {

            if (confirm("Cancel pass of loan?")) {
                $("input[name='effective_date']").val('');
                $("textarea[name='reason']").val('');
                $('#collapseForm').collapse('toggle');
            }
        } else {
            $('#collapseForm').collapse('toggle');
        }
    }

</script>
<?php
makeFoot(WEBAPP, 1);
?>