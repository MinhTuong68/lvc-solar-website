<?php
    include("../classes/news.php");
    $news_obj = new News($conn);

    if(isset($_GET['slug'])){
        $slug = $_GET['slug'];

        $item = $news_obj->getNewsBySlug($slug);

        if(!$item){
            echo "<div class='container' style='padding:100px 0; text-align:center;'><h3>Bài viết không tồn tại hoặc đã bị gỡ bỏ.</h3><a href='?page=news'>Quay lại tin tức</a></div>";
            exit;
        }

        $recentNews = $news_obj->getActiveNews();
    }else {
        header("Location: ?page=news");
        exit;
    }
?>

<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a>
            <span class="sep">/</span>
            <a href="?page=news">Tin tức</a>
            <span class="sep">/</span>
            <span class="current"><?php echo htmlspecialchars($item['title']); ?></span>
        </div>
    </div>
</div>

<div class="container detail-container">
    <div class="detail-flex">
        <main class="detail-main">
            <article class="article-content">
                <h1 class="article-title"><?php echo htmlspecialchars($item['title']); ?></h1>

                <div class="article-meta">
                    <span><i class="fa-regular fa-calendar-days"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></span>
                    <span><i class="fa-regular fa-eye"></i> <?php echo $item['views']; ?> lượt xem</span>
                    <span><i class="fa-regular fa-user"></i> Tác giả: <?php echo htmlspecialchars($item['author']); ?></span>
                </div><br>

                <?php   
                    if(!empty($item['summary'])){
                        ?>
                            <div class="article-summary">
                                <?php echo htmlspecialchars($item['summary']); ?>
                            </div>
                        <?php
                    }
                ?>
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

        <aside class="detail-sidebar">
            <div class="sidebar-widget">
                <h3 class="news-widget-title">Bài viết mới nhất</h3>
                <div class="news-widget-content">
                    <?php
                        $limit = 0;
                        if(!empty($recentNews)){
                            foreach($recentNews as $rn){
                                if($rn['slug'] == $item['slug']) continue; // Không hiện lại bài đang xem
                                if($limit >= 6) break;

                                ?>
                                    <div class="side-post-item">
                                        <a href="?page=news_detail&slug=<?php echo $rn['slug']; ?>" class="side-post-thumb">
                                            <img src="<?php echo ROOT_URL ?>/uploads/news/images/<?php echo htmlspecialchars($rn['image']); ?>" alt="">
                                        </a>
                                        <div class="side-post-info">
                                            <a href="?page=news_detail&slug=<?php echo $rn['slug']; ?>" class="side-post-title">
                                                <?php echo htmlspecialchars($rn['title']); ?>
                                            </a>
                                            <p class="popular-item-content">
                                                <?php
                                                
                                                    $clean_text = strip_tags(html_entity_decode($rn['summary']));

                                                    echo mb_strimwidth($clean_text, 0, 50, "...");
                                                ?>
                                            </p>
                                            <div class="popular-item-meta">
                                                <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($rn['created_at'])) ?></span>
                                                <span style="margin-left: 8px;"><i class="fa-solid fa-eye"></i> <?= $rn['views'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                <?php
                            }
                        }
                    ?>
                </div>
            </div>
        </aside>
    </div>
</div>