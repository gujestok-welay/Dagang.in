/**
 * Dynamic Product Filter - No Page Reload
 * Handles filter form submission via AJAX/Fetch API
 */

(function () {
  "use strict";

  // Cache DOM elements
  const filterForm = document.getElementById("filter-form");
  const productsContainer = document.getElementById("products-container");
  const productCountBadge = document.getElementById("product-count-badge");
  const paginationContainer = document.getElementById("pagination-container");
  const resetFilterBtn = document.getElementById("reset-filter-btn");

  if (!filterForm || !productsContainer) {
    console.warn("Filter form or products container not found");
    return;
  }

  /**
   * Show loading state
   */
  function showLoading() {
    productsContainer.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat produk...</p>
                </div>
            </div>
        `;

    if (productCountBadge) productCountBadge.style.display = "none";
    if (paginationContainer) paginationContainer.innerHTML = "";
  }

  /**
   * Show error message
   */
  function showError(message) {
    productsContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${message}
                </div>
            </div>
        `;
  }

  /**
   * Show empty state
   */
  function showEmptyState() {
    productsContainer.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open" style="font-size: 5rem; color: var(--text-gray); opacity: 0.3;"></i>
                    <h3 class="mt-4 text-muted">Produk Tidak Ditemukan</h3>
                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                    <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('filter-form').reset(); document.getElementById('filter-form').dispatchEvent(new Event('submit'));">
                        <i class="fas fa-redo me-2"></i>Tampilkan Semua Produk
                    </button>
                </div>
            </div>
        `;
  }

  /**
   * Render products
   */
  function renderProducts(products) {
    if (products.length === 0) {
      showEmptyState();
      return;
    }

    const productsHTML = products
      .map((product) => {
        const isLowStock = product.stock > 0 && product.stock <= 10;
        const priceFormatted = new Intl.NumberFormat("id-ID").format(
          product.price
        );
        const description =
          product.description.length > 85
            ? product.description.substring(0, 85) + "..."
            : product.description;

        const whatsappMessage = encodeURIComponent(
          `Halo, saya tertarik dengan produk *${product.name}* dengan harga Rp ${priceFormatted}`
        );

        return `
                <div class="col-lg-4 col-md-6">
                    <div class="card product-card h-100">
                        ${
                          isLowStock
                            ? '<span class="badge-new">Stok Terbatas!</span>'
                            : ""
                        }
                        
                        <img src="${product.image}" 
                            class="card-img-top"
                            alt="${product.name}"
                            style="height: 260px; object-fit: cover;"
                            onerror="this.onerror=null; this.src='../assets/images/placeholder-product.svg';">
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${product.name}</h5>
                            </div>

                            <p class="card-text text-muted" style="font-size: 0.9rem;">
                                ${description}
                            </p>

                            <span class="price-tag">
                                Rp ${priceFormatted}
                            </span>

                            <div class="mb-3">
                                <span class="stock-badge">
                                    <i class="fas fa-box me-1"></i>
                                    Stok: ${product.stock}
                                </span>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-store text-primary me-2"></i>
                                <small class="text-muted">${
                                  product.store_name
                                }</small>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=${
                                  product.id
                                }" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-eye me-2"></i>Detail
                                </a>
                                <a href="https://wa.me/${
                                  product.seller_phone
                                }?text=${whatsappMessage}"
                                    class="btn whatsapp-btn" target="_blank" rel="noopener noreferrer" title="Chat via WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
      })
      .join("");

    productsContainer.innerHTML = productsHTML;
  }

  /**
   * Update product count badge
   */
  function updateCountBadge(startItem, endItem, total) {
    if (!productCountBadge) return;

    if (total > 0) {
      productCountBadge.innerHTML = `
                Menampilkan ${startItem} - ${endItem} dari <strong>${total}</strong> produk
            `;
      productCountBadge.style.display = "inline-block";
    } else {
      productCountBadge.style.display = "none";
    }
  }

  /**
   * Update pagination
   */
  function updatePagination(paginationHTML) {
    if (!paginationContainer) return;
    paginationContainer.innerHTML = paginationHTML;

    // Add event listeners to pagination links
    const paginationLinks = paginationContainer.querySelectorAll("a.page-link");
    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const url = new URL(this.href);
        const page = url.searchParams.get("page");

        // Update form with page number
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set("page", page);

        fetchProducts(params.toString());
      });
    });
  }

  /**
   * Update reset button visibility
   */
  function updateResetButton(hasActiveFilters) {
    if (!resetFilterBtn) return;

    if (hasActiveFilters) {
      resetFilterBtn.style.display = "block";
    } else {
      resetFilterBtn.style.display = "none";
    }
  }

  /**
   * Fetch products via AJAX
   */
  function fetchProducts(queryString = "") {
    showLoading();

    // Add smooth scroll to products section
    const productsSection = document.getElementById("products");
    if (productsSection) {
      productsSection.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    fetch(`ajax_filter_products.php?${queryString}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          renderProducts(data.products);
          updateCountBadge(data.start_item, data.end_item, data.total);
          updatePagination(data.pagination_html);
          updateResetButton(data.has_active_filters);

          // Update URL without reload
          const newUrl = queryString ? `?${queryString}` : "index.php";
          history.pushState(null, "", newUrl);
        } else {
          showError("Gagal memuat produk. Silakan coba lagi.");
        }
      })
      .catch((error) => {
        console.error("Fetch error:", error);
        showError("Terjadi kesalahan saat memuat produk. Silakan coba lagi.");
      });
  }

  /**
   * Handle form submission
   */
  filterForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const params = new URLSearchParams(formData);

    // Remove empty values
    for (let [key, value] of Array.from(params.entries())) {
      if (!value) {
        params.delete(key);
      }
    }

    fetchProducts(params.toString());
  });

  /**
   * Handle reset button
   */
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener("click", function (e) {
      e.preventDefault();
      filterForm.reset();
      fetchProducts();
    });
  }

  /**
   * Handle browser back/forward buttons
   */
  window.addEventListener("popstate", function () {
    const params = new URLSearchParams(window.location.search);

    // Populate form with URL parameters
    document.getElementById("search-input").value = params.get("search") || "";
    document.getElementById("min-price-input").value =
      params.get("min_price") || "";
    document.getElementById("max-price-input").value =
      params.get("max_price") || "";
    document.getElementById("stock-filter").value = params.get("stock") || "";

    fetchProducts(params.toString());
  });
})();
