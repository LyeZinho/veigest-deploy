<?php
// Simple debug script - test login directly on database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'veigest';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Debug Login Test</h2>";
    echo "<hr>";
    
    // Test 1: User exists
    echo "<h3>Test 1: Check if users exist</h3>";
    $stmt = $pdo->query("SELECT id, username, email, status FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "✓ Users found:<br>";
        echo "<pre>" . json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "✗ No users found<br>";
    }
    
    // Test 2: Manager user exists
    echo "<h3>Test 2: Check manager user</h3>";
    $stmt = $pdo->prepare("SELECT id, username, email, status, password_hash FROM users WHERE username = ?");
    $stmt->execute(['manager']);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager) {
        echo "✓ Manager user found<br>";
        echo "ID: " . $manager['id'] . "<br>";
        echo "Email: " . $manager['email'] . "<br>";
        echo "Status: " . $manager['status'] . "<br>";
        echo "Password hash: " . substr($manager['password_hash'], 0, 20) . "...<br>";
        
        // Test 3: Check password
        echo "<h3>Test 3: Check password validation</h3>";
        $testPassword = 'manager123';
        $isValid = password_verify($testPassword, $manager['password_hash']);
        echo "Password '$testPassword' is valid: " . ($isValid ? '✓ YES' : '✗ NO') . "<br>";
    } else {
        echo "✗ Manager user NOT found<br>";
    }
    
    // Test 4: Check RBAC assignment
    echo "<h3>Test 4: Check RBAC assignment</h3>";
    $stmt = $pdo->prepare("SELECT item_name FROM auth_assignment WHERE user_id = ?");
    $stmt->execute([$manager['id'] ?? null]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if ($roles) {
        echo "✓ User roles: " . implode(', ', $roles) . "<br>";
    } else {
        echo "✗ No roles assigned<br>";
    }
    
    echo "<hr>";
    echo "<a href='index.php?r=site/login' class='btn btn-primary'>Go to Login</a>";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage();
}
?>

