<?php
    include("../classes/services.php");
    $service_obj = new Service($conn);

    if($_SERVER["REQUEST_METHOD"] == "POST" &&  isset($_POST['btn_service_type'])){
        $name = trim($_POST['service_name']);

        $result = $service_obj->addservicetype($name);
        if ($result == "success") {
            $_SESSION['toast_message'] = "Thêm danh mục thành công!";
            $_SESSION['toast_type'] = 'success';      
        }
        else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại!";
            $_SESSION['toast_type'] = 'error';  
        }
        header("Location: index.php?page=service_type"); 
        exit(); 
    }
    $list_services = $service_obj->getAllServiceTypes();
?>
<div class="wrapper">
    <div class="page-header-add">
        <h2 class="page-title-add">Quản lý thêm dịch vụ</h2>
    </div><br>
    <form action="" method="POST" class="addBrandForm">
        <div class="form-layout-add">
            <div class="form-col-right-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add">Thêm dịch vụ mới</h3><br>
                    <div class="form-group-add">
                        <label for="" class="form-label-add">Tên dich vụ</label>
                        <input type="text" name="service_name" id="service_name" class="form-control-add" required placeholder="Nhập tên dịch vụ mới">
                    </div>
                    <button class="btn-submit" name="btn_service_type" style="margin-top: 10px">Thêm</button>
                </div>
            </div>

            <div class="form-col-left-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add">Danh sách dịch vụ hiện có</h3><br>
                    <div class="form-group-add">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(!empty($list_services)){
                                        $stt=1;
                                        foreach($list_services as $item){
                                            ?>
                                                <tr>
                                                    <td class="align-middle"><?php echo $stt++ ?></td>
                                                    <td class="align-middle"><?php echo $item['service_type']?></td>
                                                    <td class="align-middle">
                                                        <a href="" class="action-btn btn-edit" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                                        <a href="" class="action-btn btn-delete" title="Xóa"><i class="fa-solid fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>  
    </form>
</div>