// Navegación entre pestañas
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        if (this.dataset.tab) {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        }
    });
});

// ========== GESTIÓN DE JUEGOS ==========
function showGameForm() {
    document.getElementById('gameForm').style.display = 'block';
    document.getElementById('gameFormTitle').textContent = 'Agregar Nuevo Juego';
    document.getElementById('gameFormElement').reset();
    document.getElementById('gameId').value = '';
}

function hideGameForm() {
    document.getElementById('gameForm').style.display = 'none';
}

function editGame(gameId) {
    fetch(`api/games.php?action=get&id=${gameId}`)
        .then(response => response.json())
        .then(result => {
            // Verificar si la respuesta es exitosa
            if (result.success === false) {
                alert('❌ ' + result.message);
                return;
            }
            
            // Acceder a los datos a través de result.data
            const game = result.data;
            document.getElementById('gameId').value = game.id;
            document.getElementById('gameName').value = game.name;
            document.getElementById('gameDescription').value = game.description || '';
            document.getElementById('gameCategory').value = game.category_id;
            document.getElementById('gameImage').value = game.image_url || '';
            document.getElementById('gameBackground').value = game.background_image || '';
            document.getElementById('gameFeatured').checked = game.featured == 1;
            document.getElementById('gameActive').checked = game.is_active == 1;
            
            document.getElementById('gameFormTitle').textContent = 'Editar Juego';
            document.getElementById('gameForm').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar el juego');
        });
}

function deleteGame(gameId) {
    if (confirm('¿Estás seguro de que quieres eliminar este juego? También se eliminarán todos sus productos.')) {
        fetch(`api/games.php?action=delete&id=${gameId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    location.reload();
                } else {
                    alert('❌ ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al eliminar el juego');
            });
    }
}

function manageProducts(gameId) {
    // Cambiar a pestaña de productos y cargar gestión específica
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector('[data-tab="products"]').classList.add('active');
    document.getElementById('products').classList.add('active');
    
    loadProductsManagement(gameId);
}

// Formulario de Juegos
document.getElementById('gameFormElement').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const gameId = document.getElementById('gameId').value;
    
    const data = {
        action: gameId ? 'update' : 'create',
        name: document.getElementById('gameName').value,
        description: document.getElementById('gameDescription').value,
        category_id: document.getElementById('gameCategory').value,
        image_url: document.getElementById('gameImage').value,
        background_image: document.getElementById('gameBackground').value,
        featured: document.getElementById('gameFeatured').checked ? 1 : 0,
        is_active: document.getElementById('gameActive').checked ? 1 : 0
    };
    
    if (gameId) {
        data.gameId = gameId;
    }
    
    fetch('api/games.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    });
});

// ========== GESTIÓN DE PRODUCTOS ==========
function loadProductsManagement(gameId) {
    fetch(`api/products.php?action=get_game_products&game_id=${gameId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('❌ ' + data.message);
                return;
            }
            
            let html = `
                <div class="card">
                    <h3>📊 Gestionar Productos: ${data.game.name}</h3>
                    <button class="btn" onclick="showProductForm(${gameId})">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                    
                    <div id="productForm" class="card" style="display: none; margin-top: 1rem;">
                        <h4 id="productFormTitle">Agregar Nuevo Producto</h4>
                        <form id="productFormElement">
                            <input type="hidden" id="productId" name="productId">
                            <input type="hidden" name="game_id" value="${gameId}">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Nombre del Producto</label>
                                    <input type="text" id="productName" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" id="productDescription" name="description" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Cantidad (CP, V-Bucks, etc.)</label>
                                    <input type="text" id="productCurrency" name="currency_amount" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Precio (Bs.)</label>
                                    <input type="number" id="productPrice" name="price" class="form-control" step="0.01" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="productAvailable" name="is_available" checked> Disponible
                                </label>
                            </div>
                            <button type="submit" class="btn">Guardar Producto</button>
                            <button type="button" class="btn" onclick="hideProductForm()" style="background: var(--medium-gray);">Cancelar</button>
                        </form>
                    </div>
                    
                    <table class="table" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productsList">
            `;
            
            if (data.products.length === 0) {
                html += `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">
                            <p>No hay productos para este juego</p>
                            <button class="btn" onclick="showProductForm(${gameId})" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Agregar Primer Producto
                            </button>
                        </td>
                    </tr>
                `;
            } else {
                data.products.forEach(product => {
                    const status = product.is_available ? 
                        '<span style="color: #48bb78;">✅ Disponible</span>' : 
                        '<span style="color: #e53e3e;">❌ No Disponible</span>';
                    
                    html += `
                        <tr>
                            <td>
                                <strong>${product.name}</strong><br>
                                <small style="color: #888;">${product.description || 'Sin descripción'}</small>
                            </td>
                            <td><strong>${product.currency_amount}</strong></td>
                            <td><strong style="color: var(--gold);">${parseFloat(product.price).toFixed(2)} Bs.</strong></td>
                            <td>${status}</td>
                            <td>
                                <button class="action-btn" onclick="editProduct(${product.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn" onclick="deleteProduct(${product.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('productsManagement').innerHTML = html;
            
            // Agregar evento al formulario de productos
            const productForm = document.getElementById('productFormElement');
            if (productForm) {
                productForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveProduct();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar los productos');
        });
}

function showProductForm(gameId) {
    document.getElementById('productForm').style.display = 'block';
    document.getElementById('productFormTitle').textContent = 'Agregar Nuevo Producto';
    document.getElementById('productFormElement').reset();
    document.getElementById('productId').value = '';
}

function hideProductForm() {
    document.getElementById('productForm').style.display = 'none';
}

function editProduct(productId) {
    fetch(`api/products.php?action=get&id=${productId}`)
        .then(response => response.json())
        .then(product => {
            // Verificar si la respuesta es un error
            if (product.success === false) {
                alert('❌ ' + product.message);
                return;
            }
            
            // Si llegamos aquí, es que product es el objeto del producto directamente
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productCurrency').value = product.currency_amount;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productAvailable').checked = product.is_available == 1;
            
            document.getElementById('productFormTitle').textContent = 'Editar Producto';
            document.getElementById('productForm').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar el producto');
        });
}

function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        fetch(`api/products.php?action=delete&id=${productId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    // Recargar la gestión de productos
                    const gameId = document.querySelector('input[name="game_id"]').value;
                    loadProductsManagement(gameId);
                } else {
                    alert('❌ ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al eliminar el producto');
            });
    }
}

function saveProduct() {
    const form = document.getElementById('productFormElement');
    const formData = new FormData(form);
    const productId = document.getElementById('productId').value;
    
    const data = {
        action: productId ? 'update' : 'create',
        game_id: formData.get('game_id'),
        name: formData.get('name'),
        description: formData.get('description'),
        currency_amount: formData.get('currency_amount'),
        price: formData.get('price'),
        is_available: formData.get('is_available') ? 1 : 0
    };
    
    if (productId) {
        data.productId = productId;
    }
    
    fetch('api/products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            const gameId = document.querySelector('input[name="game_id"]').value;
            loadProductsManagement(gameId);
        } else {
            alert('❌ ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    });
}

// ========== GESTIÓN DE CATEGORÍAS ==========
function showCategoryForm() {
    document.getElementById('categoryForm').style.display = 'block';
    document.getElementById('categoryFormTitle').textContent = 'Agregar Nueva Categoría';
    document.getElementById('categoryFormElement').reset();
    document.getElementById('categoryId').value = '';
}

function hideCategoryForm() {
    document.getElementById('categoryForm').style.display = 'none';
}

function editCategory(categoryId) {
    fetch(`api/categories.php?action=get&id=${categoryId}`)
        .then(response => response.json())
        .then(category => {
            // Verificar si la respuesta es un error
            if (category.success === false) {
                alert('❌ ' + category.message);
                return;
            }
            
            // Si llegamos aquí, es que category es el objeto de la categoría directamente
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryIcon').value = category.icon || '';
            
            document.getElementById('categoryFormTitle').textContent = 'Editar Categoría';
            document.getElementById('categoryForm').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar la categoría');
        });
}

function deleteCategory(categoryId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
        fetch(`api/categories.php?action=delete&id=${categoryId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    location.reload();
                } else {
                    alert('❌ ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al eliminar la categoría');
            });
    }
}

// Formulario de Categorías
document.getElementById('categoryFormElement').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const categoryId = document.getElementById('categoryId').value;
    
    const data = {
        action: categoryId ? 'update' : 'create',
        name: document.getElementById('categoryName').value,
        description: document.getElementById('categoryDescription').value,
        icon: document.getElementById('categoryIcon').value
    };
    
    if (categoryId) {
        data.categoryId = categoryId;
    }
    
    fetch('api/categories.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    });
});

console.log('✅ Panel administrativo cargado correctamente');