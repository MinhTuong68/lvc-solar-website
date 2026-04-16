<?php
    include_once("../classes/news.php");
    $news_obj = new News($conn);
    $allNews = $news_obj->getAllNewsAdmin();
?>

<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1>Bài viết Blog & SEO</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / </span>
            <span style="font-size: 14px; font-weight: bold;">Blog (SEO)</span>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class = "filter-bar">
                <input type="hidden" name="page" value="manage-news">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <a href="?page=add-news" class="cs-btn-addservices"><i class="fa-solid fa-plus"></i> Thêm Blog mới</a>
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?php echo htmlspecialchars($current_search); ?>">
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-service" class="cs-btn-clear">Xóa</a>
            </form>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th width="">STT</th>
                        <th width="" class="text-center">Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th width="">Ngày đăng</th>
                        <th width="">Trạng thái</th>
                        <th width="">hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if(!empty($allNews)){
                            $stt=1;
                            foreach($allNews as $rows){
                                $id = $rows['id'];
                                $image = $rows['image'];
                                $title = $rows['title'];
                                $created_at = $rows['created_at'];
                                $status = $rows['status'];

                                ?>
                                    <tr>
                                        <td>#<?php echo $stt++ ?></td>
                                        <td class="text-center">
                                            <div style="width: 80px; height: 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; padding: 2px;">
                                                <img src="../uploads/news/images/<?php echo $image ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Logo">
                                            </div>
                                        </td>
                                        <td><?php echo $title ?></td>
                                        <td><?php echo $created_at ?></td>
                                        <td><?php echo $status ?></td>
                                        <td>
                                            <a href="#" class="cs-btn cs-btn-view" title="Xem & Cập nhật"><i class="fa-solid fa-eye"></i></a>
                                            <a href="#" class="cs-btn cs-btn-delete" title="Xóa"><i class="fa-solid fa-trash-can"></i></a>
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