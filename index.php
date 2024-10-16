<?php
/*
Plugin Name: WooCommerce Custom Catalog
Description: نمایش محصولات ووکامرس به صورت کاتالوگ با قابلیت جستجو و فیلتر
Version: 1.8
Author: mohammad bagheri
*/

function custom_catalog_shortcode() {
    ob_start();
    $selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    ?>
    <div id="catalogue">
        <input type="text" id="search" placeholder="جستجو...">
        <select id="category-filter">
            <option class="no-print" value="">همه دسته‌ها</option>
             <?php
            $categories = get_terms('product_cat');
            foreach ($categories as $category) {
                $selected = ($selected_category == $category->name) ? 'selected' : '';
                echo '<option value="' . $category->name . '" ' . $selected . '>' . $category->name . '</option>';
            }
            ?>
        </select>
        <table id="product-table">
            <thead>
                <tr>
                    <th>عکس محصول</th>
                    <th>نام محصول</th>
                    <th>قیمت</th>
                    <th class="hidetma no-print" style="
    display: none;
" >دسته‌بندی</th>
                <!--
                    <th>اطلاعات بیشتر</th>
                -->
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                $loop = new WP_Query($args);
                $moretma='اطلاعات بیشتر';
                while ($loop->have_posts()) : $loop->the_post();
                    global $product;
                    echo '<tr>';
                    echo '<td><a class="more-info" data-product-id="' . $product->get_id() . ' href="' . get_permalink() . '"><h6>' . $product->get_image('medium') . '</h6></a></td>';
                    echo '<td><a class="more-info" data-product-id="' . $product->get_id() . ' href="' . get_permalink() . '"><h6>' . get_the_title() . '</h6></a></td>';
                    echo '<td><a class="more-info" data-product-id="' . $product->get_id() . ' href="' . get_permalink() . '"><h6>' . $product->get_price_html() . '<br>' .  $moretma . '</h6></a></td>';
                    echo '<td class="hidetma no-print" style="
    display: none;
" >' . wc_get_product_category_list($product->get_id()) . '</td>';
                   
                   // echo '<td><a href="#" class="more-info" data-product-id="' . $product->get_id() . '">اطلاعات بیشتر</a><div class="short-description">' . $product->get_short_description() . '</div></td>';
                    echo '</tr>';
                endwhile;
                wp_reset_query();
                ?>
            </tbody>
            

        </table>    
            <p style="
    font-size: 7px;
">
                ورژن 1.9 کاتالوگ تیماwww.t-ma.ir
                </p>

    <style>
        @media print
{    
    .no-print
    {
        display: none !important;
    }
}
    </style>
        </div>

    <div id="product-dialog" class="dialog-overlay">
        <div class="dialog-content">
            <div class="dialog-header">
                
                <h6>اطلاعات محصول</h6>
                
                <a href="#" class="close-dialog">×</a>
            </div>
            <div class="dialog-body">
                <div id="product-image"></div>
                <p id="product-full-description"></p>
                <a href="#" id="add-to-cart" class="button">افزودن به سبد خرید</a>
                <a href="#" id="view-product" class="button">دیدن محصول</a>
            </div>
        </div>
    </div>

    <style>
    .hidetma{
        display: none !important;
    }
        #catalogue table {
            width: 100%;
            border-collapse: collapse;
        }
        #catalogue th, #catalogue td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        #catalogue th {
            background-color: #f2f2f2;
        }
        .short-description {
            display: none;
        }
        .dialog-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .dialog-content {
            top:20%;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }
        .dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-dialog {
            font-size: 24px;
            text-decoration: none;
            color: #000;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        jQuery(document).ready(function($) {
            const searchInput = $('#search');
            const categoryFilter = $('#category-filter');
            const productTable = $('#product-table tbody');

            searchInput.on('input', filterProducts);
            categoryFilter.on('change', filterProducts);

            function filterProducts() {
                const searchValue = searchInput.val().toLowerCase();
                const categoryValue = categoryFilter.val();
                const rows = productTable.find('tr');

                rows.each(function() {
                    const productName = $(this).find('td').eq(1).text().toLowerCase();
                    const productCategory = $(this).find('td').eq(3).text().toLowerCase();
//alert(productCategory);
                    if (productName.includes(searchValue) && (categoryValue === '' || productCategory.includes(categoryValue))) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            filterProducts();

            $('.more-info').on('click', function(e) {
                e.preventDefault();
                const productId = $(this).data('product-id');

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_product_full_description',
                        product_id: productId
                    },
                    success: function(response) {
                        const product = JSON.parse(response);
                        $('#product-full-description').html(product.description);
                        $('#product-image').html(product.image);
                        $('#add-to-cart').attr('href', product.add_to_cart_url);
                        $('#view-product').attr('href', product.permalink);
                        $('#product-dialog').fadeIn();
                    }
                });
            });

            $('.close-dialog').on('click', function(e) {
                e.preventDefault();
                $('#product-dialog').fadeOut();
            });
        });
    </script>
    <input type="button" id="bt" onclick="printDiv('catalogue')" value="ذخیره کاتالوگ" />

<script>
 function printDiv(divId) {
     var printContents = document.getElementById(divId).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;
function myprinttmp() {
     window.print();

    // document.body.innerHTML = originalContents;
}
setTimeout(myprinttmp, 4000)
     

}
</script>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_catalog', 'custom_catalog_shortcode');

function get_product_full_description() {
    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);
    $response = array(
        'description' => $product->get_description(),
        'image' => $product->get_image('thumbnail'),
        'add_to_cart_url' => esc_url($product->add_to_cart_url()),
        'permalink' => get_permalink($product_id)
    );
    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_get_product_full_description', 'get_product_full_description');
add_action('wp_ajax_nopriv_get_product_full_description', 'get_product_full_description');