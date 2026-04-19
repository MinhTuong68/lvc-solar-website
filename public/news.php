<?php
    include("../classes/news.php");
    $news_obj = new News($conn);
    $allNews = $news_obj->getActiveNews();

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
                                        <img src="<?php echo ROOT_URL ?>/uploads/news/images/<?= htmlspecialchars($n['image']) ?>" alt="<?= htmlspecialchars($n['title']) ?>" loading="lazy">
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
                                                <img src="<?php echo ROOT_URL ?>/uploads/news/images/<?= htmlspecialchars($pn['image']) ?>" alt="<?= htmlspecialchars($pn['title']) ?>">
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
