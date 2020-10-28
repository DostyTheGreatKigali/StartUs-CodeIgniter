<style>
    .se-pre-con {
        display: none !important;
    }
</style>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h2><?php echo (!empty($title) ? $title : null) ?></h2>
                </div>
            </div>
            <div class="panel-body">
                <table class="datatable2 table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?php echo "SL No." ?></th>
                            <th><?php echo "User ID" ?></th>
                            <th><?php echo "Sponsor ID" ?></th>
                            <th><?php echo "Amount" ?></th>
                            <th><?php echo "Status" ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingPackages)) ?>
                        <?php $sl = 1; ?>
                        <?php foreach ($pendingPackages as $value) { ?>
                            <tr>
                                <td><?php echo $sl++; ?></td>
                                <td><?php echo $value->user_id; ?></td>
                                <td><?php echo $value->sponsor_id; ?></td>
                                <td><?php echo $value->buy_amount; ?></td>

                                <?php if ($value->status == 1) { ?>
                                    <td><?php echo "Requested" ?></td>
                                <?php } else if ($value->status == 2) { ?>
                                    <td><?php echo "Pending" ?></td>
                                <?php } else if ($value->status == 3) { ?>
                                    <td><?php echo "Approved" ?></td>
                                <?php } else { ?>
                                    <td><?php echo "Declined" ?></td>
                                <?php } ?>

                                <!-- <td></td> -->

                                <td>
                                    <?php
                                    // if (is_string($value->comments) && is_array(json_decode($value->comments, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false) {
                                    //     $mobiledata = json_decode($value->comments, true);
                                    //     echo '<b>OM Name:</b> ' . $mobiledata['om_name'] . '<br>';
                                    //     echo '<b>OM Phone No:</b> ' . $mobiledata['om_mobile'] . '<br>';
                                    //     echo '<b>Transaction No:</b> ' . $mobiledata['transaction_no'] . '<br>';
                                    //     echo '<b>ID Card No:</b> ' . $mobiledata['idcard_no'];
                                    // } else {
                                    //     echo $value->comments;
                                    // }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $date = date_create($value->package_request_date);
                                    echo date_format($date, "jS F Y");
                                    ?>
                                </td>
                                <?php if ($value->status == 2) { ?>
                                    <td><a class="btn btn-success btn-sm"><?php echo display('success') ?></a></td>
                                <?php } else if ($value->status == 0) { ?>
                                    <td><a class="btn btn-danger btn-sm"><?php echo display('cancel') ?></a></td>
                                <?php } else { ?>
                                    <td width="150px">
                                        <a href="<?php echo base_url() ?>backend/deposit/deposit/confirm_deposit?id=<?php echo $value->pending_package_id; ?>&user_id=<?php echo $value->user_id; ?>&set_status=1" class="btn btn-success btn-sm"><?php echo display('confirm') ?></a>
                                        <a href="<?php echo base_url() ?>backend/deposit/deposit/cancel_deposit?id=<?php echo $value->pending_package_id; ?>&user_id=<?php echo $value->user_id; ?>&set_status=2" class="btn btn-danger btn-sm"><?php echo display('cancel') ?></a>

                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php echo $links; ?>
            </div>
        </div>
    </div>
</div>