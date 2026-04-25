<?php
    include("../classes/projects.php");
    $project_obj = new Project($conn);

    if(isset($_GET['slug'])){
        $slug = $_GET['slug'];

        // 1. Lấy thông tin dự án (Hàm này đã tự động chạy lệnh +1 view ở trong class rồi)
        $item = $project_obj->getProjectBySlug($slug);

        if(!$item){
            echo "<div class='container' style='padding:100px 0; text-align:center;'><h3>Dự án không tồn tại hoặc đã bị ẩn.</h3><a href='?page=projects'>Quay lại danh sách dự án</a></div>";
            exit;
        }

        // 2. Lấy danh sách ảnh phụ (Gallery) của dự án này để hiển thị slider/lưới ảnh
        $galleryImages = $project_obj->getProjectGallery($item['id']);

        // (Tùy chọn) Có thể lấy thêm các dự án khác để làm phần "Dự án liên quan"
        // $recentProjects = $project_obj->getAllProjects(1); 

        $getviewproject = $project_obj->getPopularProjects(10);
        
    } else {
        header("Location: ?page=projects");
        exit;
    }
?>
<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span>
            <a href="?page=projects">Dự án</a><span class="sep">/</span>
            <span class="current">Chi tieết dự án</span>
        </div>
    </div>
</div><br>

<div class="container detail-container">

    <div class="project-header">
        <?php
        if($item['is_featured']==1){
            ?>
                <span class="project-badge"><i class="fa-solid fa-star"></i> Dự án Tiêu Biểu</span>
                
            <?php
        }
        ?>
        <h1 class="article-title"><?php echo htmlspecialchars($item['name']); ?></h1>
    </div><br>
     <div class="article-meta">
        <span><i class="fa-regular fa-calendar-days"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></span> 
        <span><i class="fa-solid fa-location-dot"></i> Địa chỉ: <?php echo $item['location']; ?></span>
        <span> <i class="fa-solid fa-bolt"></i> Công suất: <?php echo $item['capacity']; ?></span>
        <span><i class="fa-regular fa-eye"></i> <?php echo $item['views']; ?> lượt xem</span>
    </div><br>
    <div class="detail-flex">
        <main class="detail-main"><br>
            <article class="article-content">
                <div class="project-gallery-wrap">
                    <div class="project-main-img">
                        <img id="pjMainImage" src="<?php echo ROOT_URL; ?>uploads/projects/images/<?php echo $item['image']; ?>" alt="Thumb chính">
                    </div>

                    <div class="project-thumbs">
                        <div class="project-thumb-item active" onclick="changePjMedia(this, '<?php echo ROOT_URL; ?>uploads/projects/images/<?php echo $item['image']; ?>')">
                            <img src="<?php echo ROOT_URL; ?>uploads/projects/images/<?php echo $item['image']; ?>" alt="Thumb chính">
                        </div>
                        <?php
                            if (!empty($galleryImages)) {
                                foreach ($galleryImages as $gal) {   
                                    // Lấy tên ảnh (Tùy theo Database bạn đặt tên cột là 'image' hay 'image_path')
                                    $gal_url = ROOT_URL . "uploads/projects/gallery/" . $gal['image'];
                                    ?>
                                    <div class="project-thumb-item" onclick="changePjMedia(this, '<?php echo $gal_url; ?>')">
                                        <img src="<?php echo $gal_url; ?>" alt="Thumb phụ">
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                    </div>
                </div>
                <div class="project-short-desc">
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <p>Hệ thống được thiết kế riêng để đáp ứng nhu cầu sử dụng điện cao vào ban ngày của doanh nghiệp, giúp giảm đến 60% hóa đơn tiền điện hàng tháng, thu hồi vốn chỉ trong vòng 3.5 năm.</p>
                </div>

                <h2 class="project-content-title hr-bottom">Chi tiết dự án</h3><br>
                <div class="article-body">
                    <?php echo $item['content']; ?>
                </div>
                <div class="article-footer">
                        <div class="share-box">
                            <span>Chia sẻ:</span>
                            <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#"><i class="fa-brands fa-twitter"></i></a>
                            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        </div>
                    </div>
            </article>
        </main>
        <div><br>
            <aside class="detail-sidebar">
                <div class="sidebar-widget">
                    <h3 class="news-widget-title">Thông tin tổng quan</h3>
                    <ul class="project-info-list">
                        <li>
                            <div class="icon"><i class="fa-solid fa-user-tie"></i></div>
                            <div class="text">
                                <small>Chủ đầu tư</small>
                                <strong><?php echo $item['client'] ?></strong>
                            </div>
                        </li><br>
                        <li>
                            <div class="icon"><i class="fa-solid fa-bolt"></i></div>
                            <div class="text">
                                <small>Công suất hệ thống</small>
                                <strong style="color:var(--amber);"><?php echo $item['capacity'] ?></strong>
                            </div>
                        </li><br>
                        <li>
                            <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="text">
                                <small>Địa điểm lắp đặt</small>
                                <strong><?php echo $item['location'] ?></strong>
                            </div>
                        </li><br>
                        <li>
                            <div class="icon"><i class="fa-solid fa-calendar-check"></i></div>
                            <div class="text">
                                <small>Thời gian hoàn thành</small>
                                <strong><?php echo $item['completion_date'] ?></strong>
                            </div>
                        </li>
                    </ul>
                </div>
            </aside><br>

            <aside class="detail-sidebar">
                <div class="sidebar-widget">
                    <h3 class="news-widget-title">Dự án mới nhất</h3>
                    <div class="news-widget-content">
                        <?php
                            if (!empty($getviewproject)) {
                                usort($getviewproject, function($a, $b) {
                                    if ($a['is_featured'] != $b['is_featured']) {
                                        return $b['is_featured'] <=> $a['is_featured']; // Đẩy is_featured=1 lên trên
                                    }
                                    return $b['views'] <=> $a['views']; // Nếu trùng Nổi bật thì thằng nào view cao lên trước
                                });
                            }

                            $count_sidebar = 0;
                            foreach ($getviewproject as $rp){
                                if ($rp['id'] == $item['id']) continue; 
                                if ($count_sidebar >= 10) break;
                                $count_sidebar++;

                                ?>
                                    <div class="side-post-item">
                                        <a href="?page=detail_project&slug=<?php echo htmlspecialchars($rp['slug']); ?>" class="side-post-thumb project-icon">
                                            <img src="<?php echo ROOT_URL ?>uploads/projects/images/<?php echo htmlspecialchars($rp['image']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                            
                                            <?php if ($rp['is_featured'] == 1): ?>
                                                <span class="project-badge-icon">
                                                    Nổi bật
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="side-post-info">
                                            <a href="?page=detail_project&slug=<?php echo htmlspecialchars($rp['slug']); ?>" class="side-post-title">
                                                <?php echo htmlspecialchars($rp['name']); ?>
                                            </a>
                                            <p class="popular-item-content">
                                                <?php
                                                
                                                    $clean_text = strip_tags(html_entity_decode($rp['description']));

                                                    echo mb_strimwidth($clean_text, 0, 50, "...");
                                                ?>
                                            </p>
                                            <div class="popular-item-meta">
                                                <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($rp['created_at'])) ?></span>
                                                <span style="margin-left: 8px;"><i class="fa-solid fa-location-dot"></i> <?= $rp['location'] ?></span>
                                                <span style="margin-left: 8px;"><i class="fa-solid fa-eye"></i> <?= $rp['views'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    
    function changePjMedia(thumbElement, imgUrl) {
        // 1. Lấy thẻ ảnh to và thay đổi đường dẫn (src)
        document.getElementById('pjMainImage').src = imgUrl;

        // 2. Lấy tất cả các thẻ ảnh nhỏ
        let thumbs = document.querySelectorAll('.project-thumb-item');
        
        // 3. Xóa class 'active' khỏi tất cả ảnh nhỏ
        thumbs.forEach(function(el) {
            el.classList.remove('active');
        });
        
        // 4. Thêm class 'active' vào đúng cái ảnh nhỏ vừa click
        thumbElement.classList.add('active');
    }
</script>