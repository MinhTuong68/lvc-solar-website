<?php
    // Gọi Class và lấy danh sách dự án đang hiển thị (status = 1)
    include('../classes/projects.php');
    // Lưu ý: Biến $conn đã được khởi tạo ở file index.php bọc ngoài
    $project_obj = new Project($conn);
    $allProjects = $project_obj->getAllProjects(1); 
?>

<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span><span class="current">Dự án thi công</span>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="container">
        <h1>Dự Án Tiêu Biểu</h1>
        <p>Tự hào mang giải pháp điện năng lượng mặt trời đến hàng trăm hộ gia đình và doanh nghiệp trên toàn quốc.</p>
    </div>
</div>

<section class="projects-section">
    <div class="container">
        <?php if (!empty($allProjects)): ?>
            <div class="projects-grid">
                <?php foreach ($allProjects as $p): ?>
                    <div class="project-card">
                        <div class="project-img-wrap">
                            <a href="?page=project_detail&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>">
                                <img src="<?php echo ROOT_URL ?>uploads/projects/images/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?php echo ROOT_URL ?>uploads/projects/images/noimage.jpg';">
                            </a>
                            <?php if ($p['is_featured'] == 1): ?>
                                <span class="project-badge-hot"><i class="fa-solid fa-star"></i> Dự án Nổi bật</span>
                            <?php endif; ?>
                        </div>

                        <div class="project-content">
                            <h3 class="project-title">
                                <a href="?page=project_detail&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>"><?= e($p['name']) ?></a>
                            </h3>
                            
                            <div class="project-meta-list">
                                <div class="project-meta-item">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <span><?= !empty($p['client']) ? e($p['client']) : 'Đang cập nhật' ?></span>
                                </div>
                                <div class="project-meta-item">
                                    <i class="fa-solid fa-bolt"></i>
                                    <span><?= !empty($p['capacity']) ? e($p['capacity']) : 'Đang cập nhật' ?></span>
                                </div>
                                <div class="project-meta-item">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span><?= !empty($p['location']) ? e($p['location']) : 'Đang cập nhật' ?></span>
                                </div>
                            </div>

                            <div class="project-footer">
                                <a href="?page=project_detail&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>" class="btn-readmore">
                                    Xem chi tiết <i class="fa-solid fa-arrow-right-long"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data-state">
                <i class="fa-solid fa-solar-panel no-data-icon"></i>
                <h3>Hệ thống đang cập nhật dự án</h3>
                <p>Vui lòng quay lại sau nhé!</p>
            </div>
        <?php endif; ?>
    </div>
</section>