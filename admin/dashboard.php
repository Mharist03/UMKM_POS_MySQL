<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (function_exists('require_role')) {
    require_role('admin');
}

$user = $_SESSION['user'] ?? [];

/*
|--------------------------------------------------------------------------
| CEK STRUKTUR DATABASE
|--------------------------------------------------------------------------
*/

$tables = [];

$resultTables = $conn->query("SHOW TABLES");

if ($resultTables) {
    while ($row = $resultTables->fetch_array()) {
        $tables[] = $row[0];
    }
}

/*
|--------------------------------------------------------------------------
| STATISTIK PRODUK
|--------------------------------------------------------------------------
*/

$totalProducts = 0;
$totalStock = 0;
$lowStockCount = 0;

if (in_array('products', $tables)) {

    $result = $conn->query("SELECT COUNT(*) AS total FROM products");

    if ($result) {
        $row = $result->fetch_assoc();
        $totalProducts = (int)($row['total'] ?? 0);
    }

    $result = $conn->query("
        SELECT COALESCE(SUM(stok), 0) AS total
        FROM products
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $totalStock = (int)($row['total'] ?? 0);
    }

    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM products
        WHERE stok <= 5
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $lowStockCount = (int)($row['total'] ?? 0);
    }
}

/*
|--------------------------------------------------------------------------
| CEK KOLOM TRANSACTIONS
|--------------------------------------------------------------------------
*/

$transactionColumns = [];

if (in_array('transactions', $tables)) {

    $columns = $conn->query("SHOW COLUMNS FROM transactions");

    if ($columns) {
        while ($column = $columns->fetch_assoc()) {
            $transactionColumns[] = $column['Field'];
        }
    }
}

/*
|--------------------------------------------------------------------------
| TOTAL OMZET
|--------------------------------------------------------------------------
*/

$omzet = 0;

if (in_array('total', $transactionColumns)) {

    $result = $conn->query("
        SELECT COALESCE(SUM(total), 0) AS total_omzet
        FROM transactions
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $omzet = (float)($row['total_omzet'] ?? 0);
    }
}

/*
|--------------------------------------------------------------------------
| TOTAL TRANSAKSI
|--------------------------------------------------------------------------
*/

$totalTransactions = 0;

if (in_array('transactions', $tables)) {

    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM transactions
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $totalTransactions = (int)($row['total'] ?? 0);
    }
}

/*
|--------------------------------------------------------------------------
| TOTAL PRODUK TERJUAL
|--------------------------------------------------------------------------
*/

$totalItems = 0;

if (in_array('transaction_details', $tables)) {

    $detailColumns = [];

    $columns = $conn->query("
        SHOW COLUMNS FROM transaction_details
    ");

    if ($columns) {
        while ($column = $columns->fetch_assoc()) {
            $detailColumns[] = $column['Field'];
        }
    }

    if (in_array('quantity', $detailColumns)) {

        $result = $conn->query("
            SELECT COALESCE(SUM(quantity), 0) AS total
            FROM transaction_details
        ");

        if ($result) {
            $row = $result->fetch_assoc();
            $totalItems = (int)($row['total'] ?? 0);
        }
    }
}

/*
|--------------------------------------------------------------------------
| DATA PRODUK
|--------------------------------------------------------------------------
*/

$products = false;

if (in_array('products', $tables)) {

    $products = $conn->query("
        SELECT *
        FROM products
        ORDER BY id DESC
    ");
}

/*
|--------------------------------------------------------------------------
| DATA STOK MENIPIS
|--------------------------------------------------------------------------
*/

$lowStock = false;

if (in_array('products', $tables)) {

    $lowStock = $conn->query("
        SELECT *
        FROM products
        WHERE stok <= 5
        ORDER BY stok ASC
        LIMIT 10
    ");
}

/*
|--------------------------------------------------------------------------
| DATA TRANSAKSI
|--------------------------------------------------------------------------
*/

$transactions = false;

if (in_array('transactions', $tables)) {

    $transactions = $conn->query("
        SELECT *
        FROM transactions
        ORDER BY id DESC
        LIMIT 50
    ");
}

/*
|--------------------------------------------------------------------------
| NAMA ADMIN
|--------------------------------------------------------------------------
*/

$adminName = 'Admin';

if (isset($user['nama']) && $user['nama'] !== '') {
    $adminName = $user['nama'];
} elseif (isset($user['username']) && $user['username'] !== '') {
    $adminName = $user['username'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard Admin | UNIQ-QWE</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f6fa;
            color: #1f2937;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 250px;
            min-height: 100vh;

            background: linear-gradient(
                180deg,
                #172033,
                #111827
            );

            color: white;

            padding: 28px 18px;

            display: flex;
            flex-direction: column;
        }

        .brand {
            font-size: 25px;
            font-weight: bold;

            margin-bottom: 8px;
        }

        .brand span {
            color: #f59e0b;
        }

        .subtitle {
            color: #94a3b8;
            font-size: 12px;

            margin-bottom: 35px;

            letter-spacing: 1px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu button {
            border: none;
            background: transparent;

            color: #cbd5e1;

            padding: 14px;

            text-align: left;

            border-radius: 10px;

            cursor: pointer;

            font-size: 15px;
        }

        .menu button:hover,
        .menu button.active {
            background: #374151;
            color: white;
        }

        .logout {
            margin-top: auto;

            text-decoration: none;

            background: #991b1b;
            color: white;

            text-align: center;

            padding: 14px;

            border-radius: 10px;
        }

        /* MAIN */

        .main {
            flex: 1;

            padding: 35px;

            overflow-x: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 35px;
        }

        .topbar h1 {
            font-size: 32px;
        }

        .topbar p {
            margin-top: 7px;
            color: #64748b;
        }

        .user {
            background: white;

            padding: 13px 20px;

            border-radius: 30px;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,.05);

            font-weight: bold;
        }

        /* PAGE */

        .page {
            display: none;
        }

        .page.active {
            display: block;
        }

        /* CARDS */

        .cards {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }

        .card {
            background: white;

            padding: 23px;

            border-radius: 16px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,.05);
        }

        .card small {
            color: #64748b;
            display: block;

            margin-bottom: 12px;
        }

        .card h2 {
            font-size: 26px;
        }

        .blue {
            border-left: 5px solid #2563eb;
        }

        .green {
            border-left: 5px solid #16a34a;
        }

        .orange {
            border-left: 5px solid #f59e0b;
        }

        .red {
            border-left: 5px solid #dc2626;
        }

        /* PANEL */

        .panel {
            background: white;

            padding: 25px;

            border-radius: 16px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,.05);

            margin-bottom: 25px;
        }

        .panel h2 {
            margin-bottom: 20px;
        }

        /* TABLE */

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;

            padding: 14px;

            color: #475569;
        }

        td {
            padding: 14px;

            border-bottom:
                1px solid #e5e7eb;
        }

        /* STOCK */

        .stock-item {
            display: flex;

            justify-content: space-between;

            padding: 14px;

            border-bottom:
                1px solid #e5e7eb;
        }

        .stock-badge {
            background: #fee2e2;

            color: #b91c1c;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;
        }

        /* PRODUK */

        .product-grid {
            display: grid;

            grid-template-columns:
                repeat(auto-fill, minmax(200px, 1fr));

            gap: 20px;
        }

        .product-card {
            border:
                1px solid #e5e7eb;

            border-radius: 14px;

            overflow: hidden;

            background: white;
        }

        .product-image {
            height: 130px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #fff7ed;

            font-size: 55px;
        }

        .product-info {
            padding: 16px;
        }

        .product-info h3 {
            margin-bottom: 8px;
        }

        .product-info p {
            color: #64748b;
            font-size: 14px;
        }

        .price {
            color: #16a34a !important;

            font-size: 16px !important;

            font-weight: bold;

            margin-top: 10px;
        }

        .stock {
            margin-top: 7px;
        }

        /* EMPTY */

        .empty {
            text-align: center;

            color: #94a3b8;

            padding: 30px;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {

            .cards {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 700px) {

            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
            }

            .logout {
                margin-top: 25px;
            }

            .main {
                padding: 20px;
            }

            .topbar {
                align-items: flex-start;
                gap: 20px;

                flex-direction: column;
            }

            .cards {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            🍘 <span>UNIQ</span>-QWE
        </div>

        <div class="subtitle">
            UMKM MAKANAN TRADISIONAL
        </div>

        <div class="menu">

            <button
                class="active"
                onclick="openPage('dashboard', this)"
            >
                📊 Dashboard
            </button>

            <button
                onclick="openPage('produk', this)"
            >
                🍘 Produk
            </button>

            <button
                onclick="openPage('transaksi', this)"
            >
                💳 Transaksi
            </button>

            <button
                onclick="openPage('laporan', this)"
            >
                📈 Laporan
            </button>

        </div>

        <a
            href="../logout.php"
            class="logout"
        >
            Keluar
        </a>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <div class="topbar">

            <div>

                <h1>
                    Dashboard Admin
                </h1>

                <p>
                    Kelola produk dan pantau transaksi UNIQ-QWE.
                </p>

            </div>

            <div class="user">

                Halo,
                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>

            </div>

        </div>


        <!-- DASHBOARD -->

        <section
            id="dashboard"
            class="page active"
        >

            <div class="cards">

                <div class="card blue">

                    <small>
                        Total Produk
                    </small>

                    <h2>
                        <?= $totalProducts ?>
                    </h2>

                </div>


                <div class="card green">

                    <small>
                        Total Stok
                    </small>

                    <h2>
                        <?= $totalStock ?>
                    </h2>

                </div>


                <div class="card orange">

                    <small>
                        Stok Menipis
                    </small>

                    <h2>
                        <?= $lowStockCount ?>
                    </h2>

                </div>


                <div class="card red">

                    <small>
                        Total Omzet
                    </small>

                    <h2>
                        Rp<?= number_format(
                            $omzet,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </h2>

                </div>

            </div>


            <div class="panel">

                <h2>
                    ⚠️ Produk dengan Stok Menipis
                </h2>


                <?php if (
                    $lowStock &&
                    $lowStock->num_rows > 0
                ): ?>

                    <?php while (
                        $item = $lowStock->fetch_assoc()
                    ): ?>

                        <div class="stock-item">

                            <strong>

                                <?= htmlspecialchars(
                                    $item['nama'] ?? 'Produk',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </strong>

                            <span class="stock-badge">

                                Stok:
                                <?= (int)($item['stok'] ?? 0) ?>

                            </span>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="empty">
                        Semua stok produk masih aman.
                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- PRODUK -->

        <section
            id="produk"
            class="page"
        >

            <div class="panel">

                <h2>
                    🍘 Daftar Produk UNIQ-QWE
                </h2>

                <div class="product-grid">

                    <?php if (
                        $products &&
                        $products->num_rows > 0
                    ): ?>

                        <?php while (
                            $product = $products->fetch_assoc()
                        ): ?>

                            <div class="product-card">

                                <div class="product-image">
                                    🍘
                                </div>

                                <div class="product-info">

                                    <h3>

                                        <?= htmlspecialchars(
                                            $product['nama'] ?? 'Produk',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </h3>

                                    <p class="price">

                                        Rp<?= number_format(
                                            (float)($product['harga'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </p>

                                    <p class="stock">

                                        Stok:
                                        <?= (int)($product['stok'] ?? 0) ?>

                                    </p>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="empty">
                            Belum ada produk.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>


        <!-- TRANSAKSI -->

        <section
            id="transaksi"
            class="page"
        >

            <div class="panel">

                <h2>
                    💳 Riwayat Transaksi
                </h2>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <?php if (
                                    in_array(
                                        'tanggal',
                                        $transactionColumns
                                    )
                                ): ?>

                                    <th>Tanggal</th>

                                <?php endif; ?>

                                <th>Total</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (
                                $transactions &&
                                $transactions->num_rows > 0
                            ): ?>

                                <?php while (
                                    $transaction =
                                        $transactions->fetch_assoc()
                                ): ?>

                                    <tr>

                                        <td>

                                            #<?= (int)(
                                                $transaction['id'] ?? 0
                                            ) ?>

                                        </td>


                                        <?php if (
                                            in_array(
                                                'tanggal',
                                                $transactionColumns
                                            )
                                        ): ?>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $transaction['tanggal'] ?? '-',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </td>

                                        <?php endif; ?>


                                        <td>

                                            Rp<?= number_format(
                                                (float)(
                                                    $transaction['total'] ?? 0
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="3"
                                        class="empty"
                                    >

                                        Belum ada transaksi.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>


        <!-- LAPORAN -->

        <section
            id="laporan"
            class="page"
        >

            <div class="cards">

                <div class="card blue">

                    <small>
                        Total Transaksi
                    </small>

                    <h2>
                        <?= $totalTransactions ?>
                    </h2>

                </div>


                <div class="card green">

                    <small>
                        Total Pemasukan
                    </small>

                    <h2>

                        Rp<?= number_format(
                            $omzet,
                            0,
                            ',',
                            '.'
                        ) ?>

                    </h2>

                </div>


                <div class="card orange">

                    <small>
                        Produk Terjual
                    </small>

                    <h2>
                        <?= $totalItems ?>
                    </h2>

                </div>

            </div>


            <div class="panel">

                <h2>
                    📈 Ringkasan Laporan
                </h2>

                <p>
                    Data laporan dihitung langsung dari database
                    UMKM POS.
                </p>

            </div>

        </section>

    </main>

</div>


<script>

function openPage(pageId, button) {

    const pages =
        document.querySelectorAll('.page');

    pages.forEach(function(page) {

        page.classList.remove('active');

    });


    const buttons =
        document.querySelectorAll('.menu button');

    buttons.forEach(function(btn) {

        btn.classList.remove('active');

    });


    document
        .getElementById(pageId)
        .classList
        .add('active');


    button
        .classList
        .add('active');

}

</script>

</body>
</html>