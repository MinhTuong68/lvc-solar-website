<?php
    // Gọi Class và lấy danh sách dự án đang hiển thị (status = 1)
    include('../classes/projects.php');
    // Lưu ý: Biến $conn đã được khởi tạo ở file index.php bọc ngoài
    $project_obj = new Project($conn);

    $limit = 6;
    
    $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($current_p < 1) $current_p = 1;

    $offset = ($current_p - 1) * $limit;

    // 1. Đếm tổng số dự án đang hiển thị (status = 1)
    $sql_count = "SELECT COUNT(id) AS total FROM tbl_projects WHERE status = 1";
    $res_count = mysqli_query($conn, $sql_count);
    $total_records = mysqli_fetch_assoc($res_count)['total'];

    $total_pages = ceil($total_records / $limit); 

    $sql_data = "SELECT * FROM tbl_projects WHERE status = 1 ORDER BY is_featured DESC, id DESC LIMIT $limit OFFSET $offset";
    $res_data = mysqli_query($conn, $sql_data);
    $allProjects = [];
    if($res_data && mysqli_num_rows($res_data) > 0){
        while($row = mysqli_fetch_assoc($res_data)){
            $allProjects[] = $row;
        }
    }
?>

<div class="breadcrumb-bar"><div class="container"><div class="breadcrumb">
  <a href="/">Trang chủ</a><span class="sep">/</span><span class="current">Dự án</span>
</div></div></div>

<section class="page-hero" style="background:linear-gradient(135deg,var(--navy) 0%,#1e3a5f 100%);padding:60px 0;text-align:center">
        <div class="container">
            <div class="eyebrow" style="color:var(--amber)">THỰC TẾ - MINH BẠCH - UY TÍN</div>
            <h1 style="color:#fff;font-size:2.4rem;margin:12px 0">Dự án tiêu biểu</h1>
            <p style="color:rgba(255,255,255,.8);max-width:600px;margin:0 auto">Hàng trăm hệ thống điện mặt trời LVC Solar lắp đặt thành công tại Bạc Liêu và các tỉnh lân cận.</p>
        </div>
    </section>

<!-- Stats bar -->
<section class="projects-stats">
    <div class="container">
        <div class="stats-row">
            <div class="stat-item"><strong>500+</strong><span>Dự án hoàn thành</span></div>
            <div class="stat-item"><strong>2MWp+</strong><span>Tổng công suất lắp đặt</span></div>
            <div class="stat-item"><strong>15+</strong><span>Tỉnh thành triển khai</span></div>
            <div class="stat-item"><strong>99%</strong><span>Khách hàng hài lòng</span></div>
        </div>
    </div>
</section>

<section class="projects-section">
    <div class="container">
        <?php 
            if(!empty($allProjects)){
                ?>
                    <div class="projects-grid">
                        <?php
                            foreach ($allProjects as $p){
                                ?>
                                    <div class="project-card-full">
                                        <div class="project-card-img">
                                            <a href="?page=detail_project&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>">
                                                <img src="<?php echo ROOT_URL ?>uploads/projects/images/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?php echo ROOT_URL ?>uploads/projects/images/noimage.jpg';">
                                            </a>
                                            <?php
                                                if($p['is_featured'] == 1){
                                                    ?>
                                                        <span class="project-badge-hot"><i class="fa-solid fa-star"></i> Dự án Nổi bật</span>
                                                    <?php
                                                }
                                            ?>
                                        </div>

                                        <div class="project-card-body">
                                            <a href="?page=detail_project&slug=<?= $p['slug'] ?>">
                                                <h3 class="project-h3">
                                                    <?php
                                                            $clean_text = strip_tags(html_entity_decode($p['name']));
                                                            echo mb_strimwidth($clean_text, 0, 80, "...");
                                                        ?>   
                                            
                                                </h3> 
                                            </a>
                                            <div class="project-meta">
                                                <?php
                                                    if (!empty($p['location'])){
                                                        ?>
                                                            <span><i class="fa-solid fa-location-dot"></i> <?php echo $p['location'] ?></span>
                                                        <?php
                                                    }
                                                    if (!empty($p['capacity'])){
                                                        ?>
                                                            <span><i class="fa-solid fa-bolt"></i> <?php echo $p['capacity'] ?></span>
                                                        <?php
                                                    }
                                                    if (($p['views'])>=0){
                                                        ?>
                                                            <span><i class="fa-solid fa-eye"></i> <?php echo $p['views'] ?></span>
                                                        <?php
                                                    }
                                                ?>
                                            </div>
                                            <?php
                                                if(!empty($p['description'])){
                                                    ?>
                                                     
                                                        <p> 
                                                            <?php
                                                            $clean_text = strip_tags(html_entity_decode($p['description']));
                                                            echo mb_strimwidth($clean_text, 0, 30, "...");
                                                            ?>   
                                                        </p>
                                                    <?php
                                                }
                                            ?>
                                            <br>
                                            <div class="project-footer">
                                                <a href="?page=detail_project&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>" class="btn-readmore">
                                                    Xem chi tiết <i class="fa-solid fa-arrow-right-long"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            }
                        ?>
                    </div>
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-container">
                            <span class="page-info">(<?= $current_p ?>/<?= $total_pages ?> trang)</span>
                            <div class="pagination">
                                
                                <a href="?page=projects&p=<?= ($current_p > 1) ? ($current_p - 1) : 1 ?>" class="page-link <?= ($current_p <= 1) ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-angles-left"></i>
                                </a>

                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=projects&p=<?= $i ?>" class="page-link <?= ($current_p == $i) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <a href="?page=projects&p=<?= ($current_p < $total_pages) ? ($current_p + 1) : $total_pages ?>" class="page-link <?= ($current_p >= $total_pages) ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-angles-right"></i>
                                </a>

                            </div>
                        </div>
                    <?php endif; ?>
                <?php
            }
        ?>
    </div>
</section>