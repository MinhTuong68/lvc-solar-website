<?php
    include("../classes/news.php");
    $news_obj = new News($conn);
    $limit = 6; 
    $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($current_p < 1) $current_p = 1;

    $offset = ($current_p - 1) * $limit;

    $sql_count = "SELECT COUNT(id) AS total FROM tbl_news WHERE status = 1";
    $res_count = mysqli_query($conn, $sql_count);
    $total_records = mysqli_fetch_assoc($res_count)['total'];
    
    // 2. Tính ra tổng số trang
    $total_pages = ceil($total_records / $limit); 

    // 3. Lấy dữ liệu cho trang hiện tại có kèm chữ LIMIT
    $sql_data = "SELECT * FROM tbl_news WHERE status = 1 ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $res_data = mysqli_query($conn, $sql_data);
    $allNews = [];
    if($res_data && mysqli_num_rows($res_data) > 0){
        while($row = mysqli_fetch_assoc($res_data)){
            $allNews[] = $row;
        }
    }

    $popularNews = $news_obj->getPopularNews(5);
?>
<div class="breadcrumb-bar"><div class="container"><div class="breadcrumb">
  <a href="/">Trang chủ</a><span class="sep">/</span><span class="current">Tin tức</span>
</div></div></div>
<section class="news-section">
    <div class="container">
        <div class = "news-title">KIẾN THỨC & TIN TỨC</div>
        <h1 class = "news-h1">Blog Năng lượng mặt trời</h1>
        <p style="color:rgba(255,255,255,.8)">Kiến thức chuyên sâu, cập nhật thường xuyên từ đội ngũ kỹ sư LVC Solar</p>
    </div>
</section>
<div class="blog-container">
    <div class="container">
        <div class="blog-flex">
            <div class="blog-grid-left">
                <?php
                    if(!empty($allNews)){
                        foreach($allNews as $n){
                            ?>
                                <article class="blog-card-full">
                                    <a href="?page=news_detail&slug=<?php echo $n['slug'] ?>" class="blog-card-img">
                                        <img src="<?php echo ROOT_URL ?>uploads/news/images/<?= htmlspecialchars($n['image']) ?>" alt="<?= htmlspecialchars($n['title']) ?>" loading="lazy">
                                    </a>

                                    <div class="blog-card-body">
                                    <div class="blog-meta">
                                            <span><i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y', strtotime($n['created_at'])) ?></span>
                                            <span><i class="fa-solid fa-tag"></i>  Kiến thức solar</span>
                                            <span><i class="fa-solid fa-eye"></i> <?= $n['views'] ?> lượt xem</span>
                                        </div>
                                        
                                        <h2 class="news-card-title">
                                            <a href="?page=news_detail&slug=<?= $n['slug'] ?>">
                                                <?= htmlspecialchars($n['title']) ?>
                                            </a>
                                        </h2>

                                        <?php 
                                            if (!empty($n['summary'])){
                                                ?>
                                                    <p class="news-card-summary" style="margin: 0;">
                                                        <?php
                                                            $clean_text = strip_tags(html_entity_decode($n['summary']));
                                                            echo mb_strimwidth($clean_text, 0, 100, "...");
                                                        ?>                                          
                                                    </p>
                                                <?php
                                            }
                                            elseif(!empty($n['content'])){
                                                ?>
                                                    <p class="news-card-summary">              
                                                        <?php
                                                            $clean_text = strip_tags(html_entity_decode($n['content']));
                                                            echo mb_strimwidth($clean_text, 0, 100, "...");
                                                        ?>
                                                    </p>
                                                <?php
                                            }
                                        ?>
                                        
                                        <a href="?page=news_detail&slug=<?= $n['slug'] ?>" class="read-more">
                                            Đọc tiếp <i class="fa-solid fa-arrow-right-long"></i>
                                        </a>
                                    </div>
                                </article>
                            <?php
                        }
                    }
                    else{
                        ?>
                            <div class="news-empty">
                                <h3>Hiện chưa có bài viết nào được đăng.</h3>
                            </div>
                        <?php
                    }     
                ?>
                 <?php if ($total_pages > 1): ?>
                    <div class="pagination-container" style="grid-column: 1 / -1; width: 100%; display: flex; justify-content: center; align-items: center; margin-top: 40px; padding-bottom: 20px;">
                        
                        <span class="page-info" style="margin-right: 15px; color: var(--gray-600); font-weight: 500;">(<?= $current_p ?>/<?= $total_pages ?> trang)</span>
                        
                        <div class="pagination" style="display: flex; border: 1px solid var(--gray-200); border-radius: 8px; overflow: hidden; background: #fff;">
                            
                            <a href="?page=news&p=<?= ($current_p > 1) ? ($current_p - 1) : 1 ?>" class="page-link <?= ($current_p <= 1) ? 'disabled' : '' ?>" style="padding: 10px 16px; border-right: 1px solid var(--gray-200); color: var(--text); text-decoration: none; transition: 0.2s;">
                                <i class="fa-solid fa-angles-left"></i>
                            </a>

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=news&p=<?= $i ?>" class="page-link <?= ($current_p == $i) ? 'active' : '' ?>" style="padding: 10px 18px; border-right: 1px solid var(--gray-200); color: <?= ($current_p == $i) ? '#fff' : 'var(--text)' ?>; background: <?= ($current_p == $i) ? 'var(--navy)' : 'transparent' ?>; font-weight: <?= ($current_p == $i) ? 'bold' : 'normal' ?>; text-decoration: none; transition: 0.2s;">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?page=news&p=<?= ($current_p < $total_pages) ? ($current_p + 1) : $total_pages ?>" class="page-link <?= ($current_p >= $total_pages) ? 'disabled' : '' ?>" style="padding: 10px 16px; color: var(--text); text-decoration: none; transition: 0.2s;">
                                <i class="fa-solid fa-angles-right"></i>
                            </a>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="blog-grid-right">
                <div class="blog-popular-widget">
                    <h3 class="blog-widget-title" style="text-align: center;">
                        <i class="fa-solid fa-fire" style="color: #ef4444;"></i> 
                        BÀI VIẾT NỔI BẬT
                    </h3><br>
                    <div class="popular-news-list">
                        <?php
                            if(!empty($popularNews)){
                                foreach($popularNews as $pn){
                                    ?>
                                        <div class="popular-item">
                                            <a href="?page=news_detail&slug=<?= $pn['slug'] ?>" class="popular-item-img">
                                                <img src="<?php echo ROOT_URL ?>uploads/news/images/<?= htmlspecialchars($pn['image']) ?>" alt="<?= htmlspecialchars($pn['title']) ?>">
                                            </a>

                                            <div class="popular-item-info">
                                                <a href="?page=news_detail&slug=<?= $pn['slug'] ?>" class="popular-item-title">
                                                    <?= htmlspecialchars($pn['title']) ?>
                                                </a>
    
                                                <p class="popular-item-content">
                                                    <?php
                                                  
                                                        $clean_text = strip_tags(html_entity_decode($pn['content']));

                                                        echo mb_strimwidth($clean_text, 0, 50, "...");
                                                    ?>
                                                </p>
                                                <div class="popular-item-meta">
                                                    <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($pn['created_at'])) ?></span>
                                                    <span style="margin-left: 8px;"><i class="fa-solid fa-eye"></i> <?= $pn['views'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                }
                            }
                            else{
                                ?>
                                    <p style="color: var(--gray-400); font-size: 0.85rem; text-align: center;">Chưa có bài viết nào.</p>
                                <?php
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
