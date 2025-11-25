<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    redirect(CUSTOMER_URL . 'articles.php');
}

// Get article detail
$query = "SELECT a.*, ad.nama as author_name FROM articles a 
          JOIN admins ad ON a.created_by = ad.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    redirect(CUSTOMER_URL . 'articles.php');
}

// Get related articles (dari kategori yang sama - ambil dari judul/isi)
$related_query = "SELECT a.*, ad.nama FROM articles a 
                  JOIN admins ad ON a.created_by = ad.id 
                  WHERE a.id != ? 
                  ORDER BY a.created_at DESC 
                  LIMIT 3";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['judul']); ?> - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .article-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .article-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .article-header {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .article-header h1 {
            color: var(--primary);
            margin-bottom: 1rem;
            margin-top: 0;
        }
        .article-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .article-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .article-image {
            width: 100%;
            max-height: 500px;
            background-color: var(--accent);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-top: 1rem;
        }
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .article-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            line-height: 1.8;
        }
        .article-content p {
            margin-bottom: 1rem;
            color: #333;
        }
        .article-content h2 {
            color: var(--primary);
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .article-content h3 {
            color: var(--dark);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .article-content ol,
        .article-content ul {
            margin: 1rem 0 1rem 2rem;
        }
        .article-content li {
            margin-bottom: 0.5rem;
        }
        .related-articles {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .related-articles h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1.5rem;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .related-card {
            border: 1px solid #eee;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .related-image {
            width: 100%;
            height: 150px;
            background-color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .related-content {
            padding: 1rem;
        }
        .related-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            min-height: 2.5em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-excerpt {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .related-link:hover {
            text-decoration: underline;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 2rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .article-header,
            .article-content,
            .related-articles {
                padding: 1.5rem;
            }
            .article-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="article-layout">
        <div class="article-container">
            <a href="<?php echo CUSTOMER_URL; ?>articles.php" class="back-link">← Kembali ke Artikel</a>
            
            <div class="article-header">
                <h1><?php echo htmlspecialchars($article['judul']); ?></h1>
                <div class="article-meta">
                    <div class="article-meta-item">
                        👤 <strong><?php echo htmlspecialchars($article['author_name']); ?></strong>
                    </div>
                    <div class="article-meta-item">
                        📅 <?php echo formatDate($article['created_at']); ?>
                    </div>
                </div>
                <?php if ($article['deskripsi']): ?>
                <p style="color: #666; font-style: italic; margin: 0;">
                    <?php echo htmlspecialchars($article['deskripsi']); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($article['foto']): ?>
                <div class="article-image">
                    <img src="<?php echo ARTICLE_IMG_URL  . htmlspecialchars($article['foto']); ?>" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                </div>
                <?php endif; ?>
            </div>

            <div class="article-content">
                <?php echo $article['isi']; ?>
            </div>

            <?php if (count($related) > 0): ?>
            <div class="related-articles">
                <h3>📚 Artikel Lainnya</h3>
                <div class="related-grid">
                    <?php foreach ($related as $article_item): ?>
                    <div class="related-card">
                        <?php if ($article_item['foto']): ?>
                        <div class="related-image">
                            <img src="<?php echo ARTICLE_IMG_URL . htmlspecialchars($article_item['foto']); ?>" alt="">
                        </div>
                        <?php else: ?>
                        <div class="related-image">
                            <span style="font-size: 2rem;">📝</span>
                        </div>
                        <?php endif; ?>
                        <div class="related-content">
                            <div class="related-title"><?php echo htmlspecialchars($article_item['judul']); ?></div>
                            <div class="related-excerpt">
                                <?php echo htmlspecialchars(substr(strip_tags($article_item['isi']), 0, 80)); ?>...
                            </div>
                            <a href="<?php echo CUSTOMER_URL; ?>article-detail.php?id=<?php echo $article_item['id']; ?>" class="related-link">
                                Baca selengkapnya →
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
