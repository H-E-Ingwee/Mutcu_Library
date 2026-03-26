<?php
require_once __DIR__ . '/db.php';

session_start();

function currentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAdmin() {
    $u = currentUser();
    return $u && isset($u['role']) && $u['role'] === 'admin';
}

function getBooks() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticles() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM articles ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createBook($title, $author, $category, $description, $cover, $drive_link) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link, added_by) VALUES (?, ?, ?, ?, ?, ?, ?);');
    $stmt->execute([$title, $author, $category, $description, $cover, $drive_link, currentUser()['id'] ?? null]);
}

function createArticle($title, $author, $abstract, $link, $date, $read_time) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time, added_by) VALUES (?, ?, ?, ?, ?, ?, ?);');
    $stmt->execute([$title, $author, $abstract, $link, $date, $read_time, currentUser()['id'] ?? null]);
}

function deleteBook($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM books WHERE id=?');
    $stmt->execute([$id]);
}

function deleteArticle($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id=?');
    $stmt->execute([$id]);
}

function trackEvent($userId, $eventType, $targetType, $targetId) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO events (user_id, event_type, target_type, target_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $eventType, $targetType, $targetId]);
}

function addBookView($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE books SET view_count = view_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'view', 'book', $id);
}

function addArticleView($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'view', 'article', $id);
}

function addBookDownload($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE books SET download_count = download_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'download', 'book', $id);
}

function getStats() {
    global $pdo;
    $stats = [];
    $stats['total_books'] = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    $stats['total_articles'] = $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    $stats['total_users'] = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['total_downloads'] = $pdo->query('SELECT SUM(download_count) FROM books')->fetchColumn();
    return $stats;
}

function ensureSampleData() {
    global $pdo;
    $count = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    if ($count > 0) return;

    $sampleBooks = [
        ['A Promise Kept', 'Hatcher, Robin Lee', 'Relationships', 'A story of love, faith, and keeping promises.', 'https://via.placeholder.com/400x600?text=A+Promise+Kept', 'https://drive.google.com/open?id=1trZUZ2RbKoHEJeXNvkBAJP6o-AUBqL86'],
        ['The Assignment Vol 1: The Dream & The Destiny', 'Unknown', 'Purpose', 'Exploring dreams and destiny in life.', 'https://via.placeholder.com/400x600?text=The+Assignment+Vol+1', 'https://drive.google.com/open?id=1PfPPsRm8bI3jHlZuuJWgtv2n3hG-fAe9'],
        ['Becoming a Prayer Warrior', 'Elizabeth Alves', 'Faith', 'Learning to become a powerful prayer warrior.', 'https://via.placeholder.com/400x600?text=Becoming+a+Prayer+Warrior', 'https://drive.google.com/open?id=17-gJ2MuyyPUTDip28eoHCVCEbxrE05DE'],
        ['Sex Is Not the Problem (Lust Is)', 'Joshua Harris', 'Relationships', 'Addressing lust and sexual purity.', 'https://via.placeholder.com/400x600?text=Sex+Is+Not+the+Problem', 'https://drive.google.com/open?id=1JUVJ65VVo-av0kImNuLS2tEzmiMfrTkt'],
        ['The Great Digital Commission', 'Unknown', 'Faith', 'The commission in the digital age.', 'https://via.placeholder.com/400x600?text=The+Great+Digital+Commission', 'https://drive.google.com/open?id=1-wyJ2CXNf5WSsuxXHCiCRGGML7E7CQ2r'],
        ['In Pursuit of Purpose', 'Myles Munroe', 'Purpose', 'Discovering your life purpose.', 'https://via.placeholder.com/400x600?text=In+Pursuit+of+Purpose', 'https://drive.google.com/open?id=1PtkJc-VPXEZoThONNX1_jeAEEHl0ZdDa'],
        ['Understanding Spiritual Gifts', 'Kay Arthur', 'Faith', 'Exploring spiritual gifts in Christianity.', 'https://via.placeholder.com/400x600?text=Understanding+Spiritual+Gifts', 'https://drive.google.com/open?id=1hHcLy6zIS09j9UkPcO4zo1G2TleqjJUs'],
        ['The Sex Trap', 'Mike Murdock', 'Relationships', 'Avoiding the traps of sexuality.', 'https://via.placeholder.com/400x600?text=The+Sex+Trap', 'https://drive.google.com/open?id=1hLoVFzVm_2S8DyXZ4aPpclsSXCKINuFb'],
        ['The Purpose-Driven Life: What on Earth Am I Here For?', 'Rick Warren', 'Purpose', 'Finding purpose in life.', 'https://via.placeholder.com/400x600?text=The+Purpose-Driven+Life', 'https://drive.google.com/open?id=1irc7DKqg5857nzimBchQxfZV4UVdSN5r'],
        ['The Normal Christian Life', 'Watchman Nee', 'Faith', 'Living a normal Christian life.', 'https://via.placeholder.com/400x600?text=The+Normal+Christian+Life', 'https://drive.google.com/open?id=1tBSBDiIKCZBunQeViywNNH3MLRIBWscH'],
        ['Running with the Giants', 'John C. Maxwell', 'Leadership', 'Lessons from great leaders.', 'https://via.placeholder.com/400x600?text=Running+with+the+Giants', 'https://drive.google.com/open?id=1KQeQ14XfhRKxccPFtt3tFNWumH8S1yTr'],
        ['Relationships 101', 'John C. Maxwell', 'Relationships', 'Building healthy relationships.', 'https://via.placeholder.com/400x600?text=Relationships+101', 'https://drive.google.com/open?id=1uEpHqp1vgZHmOF-z46S3WpGRE6CsC5LC'],
        ['Everyone Communicates, Few Connect', 'John C. Maxwell', 'Leadership', 'Improving communication skills.', 'https://via.placeholder.com/400x600?text=Everyone+Communicates+Few+Connect', 'https://drive.google.com/open?id=1jgG4HdCK7g9VfIuvXrlosJA3mDYEhpmv'],
        ['Grace Is Greater', 'Kyle Idleman', 'Faith', 'The greatness of God\'s grace.', 'https://via.placeholder.com/400x600?text=Grace+Is+Greater', 'https://drive.google.com/open?id=1bSyheukdeNtLaFHaMtLyLeJ7dlS1Q17P'],
        ['Chasing Contentment', 'Erik Raymond', 'Faith', 'Finding contentment in Christ.', 'https://via.placeholder.com/400x600?text=Chasing+Contentment', 'https://drive.google.com/open?id=17y7tVeUv_R00L4wPsgWgQM_Hn2WjFNn0'],
        ['The Power of Christian Contentment', 'Andrew M. Davis', 'Faith', 'Embracing Christian contentment.', 'https://via.placeholder.com/400x600?text=The+Power+of+Christian+Contentment', 'https://drive.google.com/open?id=1rLoQI6rynfwmJeJvw4OLpntjc9bmJPr-'],
        ['Jesus CEO: Using Ancient Wisdom for Visionary Leadership', 'Laurie Beth Jones', 'Leadership', 'Leadership lessons from Jesus.', 'https://via.placeholder.com/400x600?text=Jesus+CEO', 'https://drive.google.com/open?id=13sPBTYTLifJyzVUX1hF1CzHpjhDfJnpo'],
        ['Relationship VS Fellowship', 'Unknown', 'Relationships', 'Distinguishing relationship and fellowship.', 'https://via.placeholder.com/400x600?text=Relationship+VS+Fellowship', 'https://drive.google.com/open?id=11hJddeFmd-0tkOnoCJiubQwTlOcACZSY'],
        ['GloboChrist: The Great Commission Takes a Postmodern Turn', 'Carl Raschke', 'Faith', 'The Great Commission in postmodern times.', 'https://via.placeholder.com/400x600?text=GloboChrist', 'https://drive.google.com/open?id=1eQYlpnkDybgqkAwmrSMvShM7zapH7A88'],
        ['Exploring The Riches Of Redemption', 'John Piper', 'Faith', 'Delving into redemption.', 'https://via.placeholder.com/400x600?text=Exploring+The+Riches+Of+Redemption', 'https://drive.google.com/open?id=1W7oP8YDaJSvTbD0a2TXe0qSkvhALzFv-'],
        ['All of Grace', 'Charles Spurgeon', 'Faith', 'The grace of God.', 'https://via.placeholder.com/400x600?text=All+of+Grace', 'https://drive.google.com/open?id=1j7xOz9OWT902QpwoQb3ceG9Uy4K1KEZG'],
        ['Anxious for Nothing: God\'s Cure for the Cares of Your Soul', 'John MacArthur', 'Faith', 'Overcoming anxiety with faith.', 'https://via.placeholder.com/400x600?text=Anxious+for+Nothing', 'https://drive.google.com/open?id=1N1cUI6qcjFg61aC5YQUXCbNsdXjJcORo'],
        ['The Power of Your Potential', 'John C. Maxwell', 'Leadership', 'Unlocking your potential.', 'https://via.placeholder.com/400x600?text=The+Power+of+Your+Potential', 'https://drive.google.com/open?id=1cYPYSPQWzzexqy0My5H0sMUU41YJvlpt'],
        ['Living the Cross Centered Life', 'C.J. Mahaney', 'Faith', 'Life centered on the cross.', 'https://via.placeholder.com/400x600?text=Living+the+Cross+Centered+Life', 'https://drive.google.com/open?id=1bFQQO91wsJy7aeKj0vXAUjOnaehdjQjZ'],
        ['The Proverbs 31 Woman', 'Mike Murdock', 'Relationships', 'The ideal woman from Proverbs.', 'https://via.placeholder.com/400x600?text=The+Proverbs+31+Woman', 'https://drive.google.com/open?id=17QjHHomc4FnRzDtoxeFeOXZtmFksxO8F'],
        ['Finding Your Purpose in Life', 'Mike Murdock', 'Purpose', 'Discovering life purpose.', 'https://via.placeholder.com/400x600?text=Finding+Your+Purpose+in+Life', 'https://drive.google.com/open?id=1G5bVIeo6Y3ZC4Yv1MOzcgWbeV69mdgeG'],
        ['How Successful People Think', 'John C. Maxwell', 'Leadership', 'Thinking like successful people.', 'https://via.placeholder.com/400x600?text=How+Successful+People+Think', 'https://drive.google.com/open?id=1_iLyCpr3eD8djvQcXa7SjTjhVcm89CwS'],
        ['Grace: The DNA of God', 'Tony Cooke', 'Faith', 'Grace as God\'s DNA.', 'https://via.placeholder.com/400x600?text=Grace+The+DNA+of+God', 'https://via.placeholder.com/400x600?text=Grace+The+DNA+of+God', 'https://drive.google.com/open?id=1doWsSI_73OgqO_KhvuBFWkKqrux3G9EH'],
        ['Good Leaders Ask Great Questions', 'John C. Maxwell', 'Leadership', 'Leadership through questions.', 'https://via.placeholder.com/400x600?text=Good+Leaders+Ask+Great+Questions', 'https://drive.google.com/open?id=1w-4dpI0KG5hXzE3un2VsP2K5s1WyR3SN'],
        ['New Creation Realities', 'E.W. Kenyon', 'Faith', 'Realities of the new creation.', 'https://via.placeholder.com/400x600?text=New+Creation+Realities', 'https://drive.google.com/open?id=195E9vlgYKAlshyEACYkYaRcVspvYbKbA'],
        ['Redemption Accomplished and Applied', 'John Murray', 'Faith', 'The work of redemption.', 'https://via.placeholder.com/400x600?text=Redemption+Accomplished+and+Applied', 'https://drive.google.com/open?id=1cMdkZPqM2yJffQIvuXqOjq7NyKCmwwgG'],
        ['Healing the Scars of Emotional Abuse', 'Gregory L. Jantz', 'Relationships', 'Healing from emotional abuse.', 'https://via.placeholder.com/400x600?text=Healing+the+Scars+of+Emotional+Abuse', 'https://drive.google.com/open?id=1jEVJwANEwjPDbb2_2QkR2srs1c9QBLgQ'],
        ['The New Man Seminar Workbook/Study Guide', 'Unknown', 'Faith', 'Study guide for the new man.', 'https://via.placeholder.com/400x600?text=The+New+Man+Seminar+Workbook', 'https://drive.google.com/open?id=1JH0Zu_Y6Y81OTfsHiiQEe4OSkCWLYEVJ'],
        ['The Reality Of Sonship', 'Curry R. Blake', 'Faith', 'Understanding sonship in Christ.', 'https://via.placeholder.com/400x600?text=The+Reality+Of+Sonship', 'https://drive.google.com/open?id=1zHY7DhV82-VC1mn2-9U5vXCBZReWIN2L'],
        ['What is the Great Commission?', 'R.C. Sproul', 'Faith', 'Explaining the Great Commission.', 'https://via.placeholder.com/400x600?text=What+is+the+Great+Commission', 'https://drive.google.com/open?id=1-PROrcHteH4Qq2QGlSojuT9C8YL7Q1sJ'],
    ];

    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?,?,?,?,?,?)');
    foreach ($sampleBooks as $b) { $stmt->execute($b); }

    $sampleArticles = [
        ['The Joy of the Lord is Your Strength','John Piper','An exposition on Nehemiah 8 and how finding joy in God is the fuel for Christian living.','#article-link','Oct 12, 2025','5 min read'],
        ['Christian Leadership in the 21st Century','Albert Mohler','Challenges and opportunities for young leaders navigating a secularizing culture.','#article-link','Nov 05, 2025','8 min read']
    ];
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?,?,?,?,?,?)');
    foreach ($sampleArticles as $a) { $stmt->execute($a); }
}

ensureSampleData();
