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
            if (result.success === false) {
                alert('❌ ' + result.message);
                return;
            }
            
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
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector('[data-tab="products"]').classList.add('active');
    document.getElementById('products').classList.add('active');
    
    document.getElementById('gameSelector').value = gameId;
    loadProductsForGame(gameId);
}

// Formulario de Juegos
document.getElementById('gameFormElement').addEventListener('submit', function(e) {
    e.preventDefault();
    
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

function loadProductsForGame(gameId) {
    if (!gameId) {
        document.getElementById('productsManagement').style.display = 'none';
        return;
    }
    
    document.getElementById('productsManagement').style.display = 'block';
    document.getElementById('selectedGameId').value = gameId;
    
    resetProductForm();
    
    fetch(`api/products.php?action=get_game_products&game_id=${gameId}`)
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                document.getElementById('productsList').innerHTML = 
                    '<p style="color: #e53e3e;">Error al cargar productos: ' + result.message + '</p>';
                return;
            }
            
            updateProductsList(result.products, result.game);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('productsList').innerHTML = 
                '<p style="color: #e53e3e;">Error de conexión al cargar productos</p>';
        });
}

function updateProductsList(products, game) {
    const productsList = document.getElementById('productsList');
    
    if (products.length === 0) {
        productsList.innerHTML = '<p>No hay productos para este juego.</p>';
        return;
    }
    
    let html = `
        <p><strong>Juego:</strong> ${game.name}</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    products.forEach(product => {
        const status = product.is_available ? 
            '<span style="color: #48bb78;">✅ Disponible</span>' : 
            '<span style="color: #e53e3e;">❌ No disponible</span>';
        
        html += `
            <tr>
                <td>
                    <strong>${escapeHtml(product.name)}</strong><br>
                    <small style="color: #888;">${escapeHtml(product.description || 'Sin descripción')}</small>
                </td>
                <td><strong>${escapeHtml(product.currency_amount)}</strong></td>
                <td><strong style="color: var(--gold);">${parseFloat(product.price).toFixed(2)} Bs.</strong></td>
                <td>${status}</td>
                <td>
                    <button class="action-btn" onclick="editExistingProduct(${product.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn" onclick="deleteExistingProduct(${product.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    productsList.innerHTML = html;
}

function editExistingProduct(productId) {
    fetch(`api/products.php?action=get&id=${productId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success === false) {
                alert('❌ ' + result.message);
                return;
            }
            
            const product = result.data;
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productCurrency').value = product.currency_amount;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productAvailable').checked = product.is_available == 1;
            
            document.getElementById('productFormTitle').textContent = 'Editar Producto';
            document.getElementById('productForm').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar el producto');
        });
}

function deleteExistingProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        fetch(`api/products.php?action=delete&id=${productId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    const gameId = document.getElementById('selectedGameId').value;
                    loadProductsForGame(gameId);
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

function resetProductForm() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productFormTitle').textContent = 'Agregar Nuevo Producto';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const productId = document.getElementById('productId').value;
    const gameId = document.getElementById('selectedGameId').value;
    
    if (!gameId) {
        alert('❌ Primero selecciona un juego');
        return;
    }
    
    const data = {
        action: productId ? 'update' : 'create',
        game_id: gameId,
        name: document.getElementById('productName').value,
        description: document.getElementById('productDescription').value,
        currency_amount: document.getElementById('productCurrency').value,
        price: document.getElementById('productPrice').value,
        is_available: document.getElementById('productAvailable').checked ? 1 : 0
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
            resetProductForm();
            loadProductsForGame(gameId);
        } else {
            alert('❌ ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    });
});

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
        .then(result => {
            if (result.success === false) {
                alert('❌ ' + result.message);
                return;
            }
            
            const category = result.data;
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

document.getElementById('categoryFormElement').addEventListener('submit', function(e) {
    e.preventDefault();
    
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

// ========== GESTIÓN DE ADMINISTRADORES ==========

function showAdminForm() {
    document.getElementById('adminForm').style.display = 'block';
    document.getElementById('adminFormTitle').textContent = 'Agregar Nuevo Administrador';
    document.getElementById('adminFormElement').reset();
    document.getElementById('adminId').value = '';
    document.getElementById('adminRole').value = 'admin';
}

function hideAdminForm() {
    document.getElementById('adminForm').style.display = 'none';
}

function editAdmin(adminId) {
    fetch(`api/admins.php?action=get&id=${adminId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success === false) {
                alert('❌ ' + result.message);
                return;
            }
            
            const admin = result.data;
            document.getElementById('adminId').value = admin.id;
            document.getElementById('adminUserId').value = admin.user_id;
            document.getElementById('adminRole').value = admin.role;
            
            document.getElementById('adminFormTitle').textContent = 'Editar Administrador';
            document.getElementById('adminForm').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar el administrador');
        });
}

function deleteAdmin(adminId) {
    if (confirm('¿Estás seguro de que quieres eliminar este administrador?')) {
        fetch(`api/admins.php?action=delete&id=${adminId}`)
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
                alert('❌ Error al eliminar el administrador');
            });
    }
}

document.getElementById('adminFormElement').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const adminId = document.getElementById('adminId').value;
    const userId = document.getElementById('adminUserId').value;
    
    if (!userId) {
        alert('❌ Por favor selecciona un usuario');
        return;
    }
    
    const data = {
        action: adminId ? 'update' : 'create',
        user_id: userId,
        role: document.getElementById('adminRole').value
    };
    
    if (adminId) {
        data.adminId = adminId;
    }
    
    fetch('api/admins.php', {
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