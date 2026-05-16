<?php
include("../classes/order.php");
$orderObj = new Order($conn);

if (!isset($_GET['id'])) {
    header("Location: ?page=order_history");
    exit;
}

$id = (int)$_GET['id'];
$order = $orderObj->getOrderByID($id);

$authorized_phones = isset($_SESSION['authorized_phones']) ? $_SESSION['authorized_phones'] : [];

if (!$order || !in_array($order['customer_phone'], $authorized_phones)) {
    $_SESSION['toast_message'] = "Bạn không có quyền xem hóa đơn này!";
    $_SESSION['toast_type'] = "error";
    header("Location: ?page=order_history");
    exit;
}

$details = $orderObj->getOrderDetails($id);

$company = [
    'name' => 'CÔNG TY TNHH CÔNG NGHỆ - ĐẦU TƯ XÂY DỰNG - BĐS - NĂNG LƯỢNG LVC',
    'short_name' => 'LVC SOLAR',
    'tax_code' => '1900692997',
    'director' => 'LÊ VĂN CHỜ',
    'address' => 'Số 01, Đường Tôn Đức Thắng, Phường Vĩnh Trạch, Tỉnh Cà Mau',
    'phone' => '02913.678.186',
    'hotline' => '0945671536',
    'email' => 'vanchocomputer@gmail.com'
];

function money($number) {
    return number_format($number, 0, ',', '.') . 'đ';
}

function paymentText($method) {
    if ($method == 'bank_transfer') {
        return 'Chuyển khoản ngân hàng';
    }
    return 'Thanh toán khi nhận hàng (COD)';
}

function paymentStatusText($status) {
    if ($status == 'paid') {
        return 'Đã thanh toán';
    }
    return 'Chưa thanh toán';
}
?>

<style>
    .invoice-page {
        max-width: 900px;
        margin: 30px auto;
        background: #fff;
        color: #111827;
        padding: 30px;
        font-family: Arial, sans-serif;
        border: 1px solid #e5e7eb;
    }

    .invoice-top {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        border-bottom: 3px solid #0ea5e9;
        padding-bottom: 20px;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        color: #0369a1;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .company-info p,
    .customer-info p {
        margin: 4px 0;
        font-size: 14px;
    }

    .invoice-title {
        text-align: right;
    }

    .invoice-title h1 {
        margin: 0;
        color: #dc2626;
        font-size: 28px;
    }

    .invoice-title p {
        margin: 6px 0;
        font-size: 14px;
    }

    .invoice-section {
        margin-top: 25px;
    }

    .section-title {
        font-size: 16px;
        font-weight: bold;
        color: #0369a1;
        margin-bottom: 10px;
        border-left: 4px solid #0ea5e9;
        padding-left: 10px;
    }

    .invoice-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .info-box {
        border: 1px solid #e5e7eb;
        padding: 15px;
        border-radius: 8px;
        background: #f9fafb;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .invoice-table th {
        background: #0369a1;
        color: white;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #0369a1;
    }

    .invoice-table td {
        padding: 10px;
        border: 1px solid #d1d5db;
        font-size: 14px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .summary-box {
        width: 320px;
        margin-left: auto;
        margin-top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #d1d5db;
        font-size: 15px;
    }

    .summary-total {
        font-size: 20px;
        font-weight: bold;
        color: #dc2626;
    }

    .invoice-note {
        margin-top: 25px;
        padding: 12px;
        background: #fefce8;
        border: 1px solid #fde68a;
        border-radius: 8px;
        font-size: 14px;
    }

    .signature-area {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 45px;
        text-align: center;
    }

    .signature-box {
        min-height: 120px;
    }

    .signature-title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .signature-small {
        font-size: 13px;
        color: #6b7280;
    }

    .invoice-actions {
        max-width: 900px;
        margin: 20px auto;
        text-align: right;
    }

    .btn-print-invoice {
        background: #0369a1;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-back-invoice {
        display: inline-block;
        background: #6b7280;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 8px;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .invoice-page,
        .invoice-page * {
            visibility: visible;
        }

        .invoice-page {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: none;
            margin: 0;
            border: none;
            padding: 20px;
        }

        .invoice-actions {
            display: none;
        }
    }
    /* ── MOBILE RESPONSIVE ── */
    @media (max-width: 600px) {
        .invoice-actions {
            margin: 12px 16px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .invoice-page {
            margin: 0;
            padding: 16px;
            border: none;
            border-radius: 0;
        }

        .invoice-top {
            flex-direction: column;
            gap: 14px;
            padding-bottom: 14px;
        }

        .company-name {
            font-size: 14px;
        }

        .invoice-title {
            text-align: left;
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
        }

        .invoice-title h1 {
            font-size: 20px;
        }

        .invoice-info-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .info-box {
            padding: 12px;
        }

        .section-title {
            font-size: 14px;
        }

        /* Bảng sản phẩm: ẩn cột đơn giá + SL trên mobile,
        gộp vào dòng phụ bên dưới tên */
        .invoice-table thead th:nth-child(3),
        .invoice-table thead th:nth-child(4) {
            display: none;
        }

        .invoice-table td:nth-child(3),
        .invoice-table td:nth-child(4) {
            display: none;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 8px 6px;
            font-size: 13px;
        }

        /* Cột thành tiền về bên phải */
        .invoice-table td:last-child {
            white-space: nowrap;
        }

        .summary-box {
            width: 100%;
            margin-top: 16px;
        }

        .summary-row {
            font-size: 14px;
            padding: 6px 0;
        }

        .summary-total {
            font-size: 17px;
        }

        .signature-area {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .signature-title {
            font-size: 13px;
        }

        .btn-print-invoice,
        .btn-back-invoice {
            padding: 8px 14px;
            font-size: 13px;
        }
    }

    @media (max-width: 380px) {
        .signature-area {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="invoice-actions">
    <a href="?page=detail_order&id=<?= $id ?>" class="btn-back-invoice">Quay lại</a>
    <button onclick="window.print()" class="btn-print-invoice">In / Lưu PDF</button>
</div>

<div class="invoice-page">
    <div class="invoice-top">
        <div class="company-info">
            <div class="company-name"><?= $company['name'] ?></div>
            <p><strong>Tên giao dịch:</strong> <?= $company['short_name'] ?></p>
            <p><strong>MST:</strong> <?= $company['tax_code'] ?></p>
            <p><strong>Đại diện:</strong> <?= $company['director'] ?></p>
            <p><strong>Địa chỉ:</strong> <?= $company['address'] ?></p>
            <p><strong>Điện thoại:</strong> <?= $company['phone'] ?> - Hotline: <?= $company['hotline'] ?></p>
            <p><strong>Email:</strong> <?= $company['email'] ?></p>
        </div>

        <div class="invoice-title">
            <h1>HÓA ĐƠN BÁN HÀNG</h1>
            <p><strong>Mã đơn:</strong> #<?= $order['order_code'] ?></p>
            <p><strong>Ngày lập:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
    </div>

    <div class="invoice-section invoice-info-grid">
        <div class="info-box customer-info">
            <div class="section-title">Thông tin khách hàng</div>
            <p><strong>Khách hàng:</strong> <?= $order['customer_name'] ?></p>
            <p><strong>Số điện thoại:</strong> <?= $order['customer_phone'] ?></p>
            <p><strong>Email:</strong> <?= !empty($order['customer_email']) ? $order['customer_email'] : 'Không có' ?></p>
            <p><strong>Địa chỉ:</strong> <?= $order['customer_address'] ?></p>
        </div>

        <div class="info-box customer-info">
            <div class="section-title">Thông tin thanh toán</div>
            <p><strong>Phương thức:</strong> <?= paymentText($order['payment_method']) ?></p>
            <p><strong>Trạng thái:</strong> <?= paymentStatusText($order['payment_status']) ?></p>
            <p><strong>Ghi chú:</strong> <?= !empty($order['note']) ? $order['note'] : 'Không có ghi chú' ?></p>
        </div>
    </div>

    <div class="invoice-section">
        <div class="section-title">Chi tiết sản phẩm / dự án</div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 45px;">STT</th>
                    <th>Tên sản phẩm / hạng mục</th>
                    <th style="width: 120px;">Đơn giá</th>
                    <th style="width: 90px;">Số lượng</th>
                    <th style="width: 140px;">Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                <?php
                    $stt = 1;
                    $subTotal = 0;

                    foreach ($details as $item) {
                        $lineTotal = $item['price'] * $item['quantity'];
                        $subTotal += $lineTotal;
                ?>
                    <tr>
                        <td class="text-center"><?= $stt++ ?></td>
                        <td>
                            <strong><?= $item['product_name'] ?></strong><br>
                            <small>Mã SP: <?= $item['product_id'] ?></small>
                        </td>
                        <td class="text-right"><?= money($item['price']) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-right"><?= money($lineTotal) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span>Tạm tính:</span>
                <strong><?= money($subTotal) ?></strong>
            </div>

            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <strong>Miễn phí</strong>
            </div>

            <div class="summary-row summary-total">
                <span>Tổng cộng:</span>
                <span><?= money($order['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <div class="invoice-note">
        <strong>Ghi chú:</strong> Hóa đơn được tạo từ hệ thống đặt hàng của <?= $company['short_name'] ?>.
        Vui lòng kiểm tra kỹ thông tin sản phẩm, số lượng và tổng tiền trước khi thanh toán.
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-title">Người mua hàng</div>
            <div class="signature-small">(Ký và ghi rõ họ tên)</div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Đại diện <?= $company['short_name'] ?></div>
            <div class="signature-small">(Ký, đóng dấu và ghi rõ họ tên)</div>
        </div>
    </div>
</div>