<?php
require_once('includes/config.php');
require_once('header.php');

// Get pets using PDO
$stmt = $pdo->prepare("SELECT * FROM pets WHERE status = 'available' ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$available_pets = $stmt->fetchAll();
?>

<style>
    .hero {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
        margin-bottom: 2rem;
        border-radius: 20px;
    }
    
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        z-index: 0;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.45), rgba(0,0,0,0.3));
        z-index: 1;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 550px;
        padding: 1.8rem 2.5rem;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(12px);
        border-radius: 35px;
        margin: 1.5rem;
        border: 1px solid rgba(255,255,255,0.2);
        animation: fadeInUp 0.8s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .hero h2 {
        color: white;
        font-size: 2.2rem;
        margin-bottom: 0.4rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        font-weight: 700;
    }
    
    .hero p {
        color: rgba(255,255,255,0.95);
        font-size: 1rem;
        text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
    }
    
    .hero-badge {
        display: inline-block;
        background: #ff6b8b;
        color: white;
        padding: 0.2rem 0.8rem;
        border-radius: 25px;
        font-size: 0.7rem;
        margin-top: 0.8rem;
        font-weight: 500;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
        color: #ff6b8b;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .pet-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .pet-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(255,107,139,0.1);
        transition: transform 0.3s;
        text-decoration: none;
    }
    
    .pet-card:hover {
        transform: translateY(-5px);
    }
    
    .pet-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .pet-info {
        padding: 1rem;
    }
    
    .pet-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #5a2a3a;
    }
    
    .pet-breed {
        font-size: 0.85rem;
        color: #888;
        margin: 0.3rem 0;
    }
    
    .pet-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ff6b8b;
        margin-top: 0.5rem;
    }
    
    .btn-add-to-cart {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        margin: 10px;
        width: calc(100% - 20px);
        transition: background 0.3s;
    }
    
    .btn-add-to-cart:hover {
        background: #218838;
    }
    
    @media (max-width: 768px) {
        .hero {
            min-height: 300px;
        }
        .hero-content {
            margin: 1rem;
            padding: 1.2rem;
            max-width: 90%;
        }
        .hero h2 {
            font-size: 1.3rem;
        }
        .hero p {
            font-size: 0.8rem;
        }
        .container {
            padding: 1rem;
        }
    }
</style>

<div class="hero">
    <img src="https://images.pexels.com/photos/97082/weimaraner-puppy-dog-snout-97082.jpeg?w=1400" alt="Bulldog Face Close Up" class="hero-bg">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h2>Welcome to Mimie's Pet Hub 🐾</h2>
        <p>Find your perfect furry companion in Zimbabwe</p>
        <div class="hero-badge">
            <i class="fas fa-paw"></i> Meet our friendly bulldog!
        </div>
    </div>
</div>

<div class="container">
    <h2 class="section-title">Available Pets</h2>
    <div class="pet-grid">
        <?php if(count($available_pets) > 0): ?>
            <?php foreach($available_pets as $pet): ?>
            <div class="pet-card">
                <a href="view-pet.php?id=<?php echo $pet['id']; ?>" style="text-decoration: none;">
                    <img src="<?php echo htmlspecialchars($pet['image_url'] ?? 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400'); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                    <div class="pet-info">
                        <div class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></div>
                        <div class="pet-breed"><?php echo htmlspecialchars($pet['breed'] ?? $pet['species'] ?? 'Mixed'); ?></div>
                        <div class="pet-price">$<?php echo number_format($pet['price'], 2); ?></div>
                    </div>
                </a>
                <button class="btn-add-to-cart" onclick="addToCart(<?php echo $pet['id']; ?>)">
                    🛒 Add to Cart
                </button>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1; color: #8a5a6a;">No pets available yet. Check back soon!</p>
        <?php endif; ?>
    </div>
</div>

<script>
function addToCart(petId) {
    window.location.href = 'cart.php?add=' + petId;
}
</script>

<?php require_once('footer.php'); ?>