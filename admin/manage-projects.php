<div class="wrapper">
    <div class="cs-page-wrapper">
         <div class="cs-header">
            <h1>Bài viết dự án</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / </span>
            <span style="font-size: 14px; font-weight: bold;">Dự án</span>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class = "filter-bar">
                <input type="hidden" name="page" value="manage-news">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <a href="?page=manage-add-projects" class="cs-btn-addservices"><i class="fa-solid fa-plus"></i> Thêm dự án mới</a>
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?php echo htmlspecialchars($current_search); ?>">
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-service" class="cs-btn-clear">Xóa</a>
            </form>
        </div>

        <div class="cs-table-container">
            
        </div>
    </div>
</div>