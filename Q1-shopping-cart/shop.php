
<?php
session_start();
require_once "connection.php";

if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

// Fetch categories for the dropdown
$categories = $conn->query(
    "SELECT id, name FROM categories ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Store</title>
</head>
<body>

<h1>Welcome, <?= htmlspecialchars($_SESSION["customer_name"]) ?>!</h1>

<a href="customer_logout.php">Logout</a>


<h2>Available Products</h2>

<form id="filterForm">
    <label for="category_id">Filter by Category:</label>

    <select name="category_id" id="category_id">
        <option value="">All Categories</option>

        <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
</form>

<p><a href="cart.php">View Shopping Cart</a></p>

<a href="my_orders.php">My Orders</a>

<div id="products" style="display:flex; flex-wrap:wrap; gap:20px;"></div>

<div id="pagination" style="margin-top:20px;"></div>

<p id="message"></p>

<script>
const productsContainer = document.getElementById("products");
const paginationContainer = document.getElementById("pagination");
const message = document.getElementById("message");
const categorySelect = document.getElementById("category_id");

async function loadProducts(page = 1) {
    productsContainer.innerHTML = "Loading...";
    paginationContainer.innerHTML = "";
    message.textContent = "";

    const params = new URLSearchParams({ page });

    if (categorySelect.value) {
        params.set("category_id", categorySelect.value);
    }

    try {
        const response = await fetch("fetch_products.php?" + params.toString());

        if (!response.ok) {
            throw new Error("Unable to load products.");
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        productsContainer.innerHTML = "";

        if (data.products.length === 0) {
            productsContainer.textContent = "No products found.";
            return;
        }

        data.products.forEach(product => {
            const card = document.createElement("div");
            card.style.cssText =
                "border:1px solid #ccc;padding:15px;width:220px;";

            if (product.image) {
                const image = document.createElement("img");
                image.src = "uploads/" + encodeURIComponent(product.image);
                image.alt = product.name;
                image.style.cssText =
                    "width:150px;height:120px;object-fit:contain;";
                card.appendChild(image);
            }

            const name = document.createElement("h3");
            name.textContent = product.name;
            card.appendChild(name);

            const category = document.createElement("p");
            category.textContent = "Category: " + product.category_name;
            card.appendChild(category);

            const description = document.createElement("p");
            description.textContent = product.description || "";
            card.appendChild(description);

            const price = document.createElement("p");
            price.textContent =
                "Price: ₹" + Number(product.price).toFixed(2);
            card.appendChild(price);

            const stock = document.createElement("p");
            stock.textContent = "Stock: " + product.stock;
            card.appendChild(stock);

            if (Number(product.stock) > 0) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "cart.php";

                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "product_id";
                input.value = product.id;

                const button = document.createElement("button");
                button.type = "submit";
                button.textContent = "Add to Cart";

                form.append(input, button);
                card.appendChild(form);
            } else {
                const outOfStock = document.createElement("p");
                outOfStock.textContent = "Out of Stock";
                card.appendChild(outOfStock);
            }

            productsContainer.appendChild(card);
        });

        for (let p = 1; p <= data.total_pages; p++) {
            const button = document.createElement("button");
            button.textContent = p;
            button.style.marginRight = "5px";
            button.disabled = p === data.current_page;
            button.addEventListener("click", () => loadProducts(p));
            paginationContainer.appendChild(button);
        }
    } catch (error) {
        productsContainer.innerHTML = "";
        message.textContent = error.message;
    }
}

document.getElementById("filterForm").addEventListener("submit", event => {
    event.preventDefault();
    loadProducts(1);
});

loadProducts();
</script>


</body>
</html>
