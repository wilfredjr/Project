<?php
    require_once("support/config.php");
    if(!isLoggedIn()){
        toLogin();
        die();
    }
    if(!AllowUser(array(1,4)))
    {
        redirect("index.php");
    }

    $data=$con->myQuery("SELECT id,name,is_convertable,is_pay FROM leaves WHERE is_deleted=0");
    makeHead("Type of Leaves");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
            Type of Leaves
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-12'>
                <?php Alert(); ?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class='col-ms-12 text-right'>
                                    <a href='frm_leave_type.php' class='btn btn-warning'> Create New <span class='fa fa-plus'></span> </a>
                                </div>
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                            <th class='text-center' style="width:40%">Name</th>
                                            <th class='text-center' style="width:20%">With Pay</th>
                                            <th class='text-center' style="width:20%">Convertable To Cash </th>
                                            <th class='text-center' style="width:20%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        while($row = $data->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name'])?></td>
                                            <td><?php echo $row['is_pay']==1?"YES":"NO"; ?></td>
                                            <td><?php echo $row['is_convertable']==1?"YES":"NO"; ?></td>
                                            <td class='text-center'>
                                                <a href='frm_leave_type.php?id=<?php echo $row['id']?>' class='btn-s btn-success btn-sm'><span class='fa fa-pencil'></span></a>
                                                <a href='delete.php?t=ltyp&id=<?php echo $row['id']?>' onclick="return confirm('This record will be deleted.')" class='btn-s btn-danger btn-sm'><span class='fa fa-trash'></span></a>
                                            </td>
                                        </tr>
                                    <?php
                                        endwhile;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(function () 
    {
        $('#ResultTable').DataTable({
            "columnDefs": [{"orderable":false, "targets":3}]
        });
    });
</script>
<?php
    Modal();
    makeFoot();
?>