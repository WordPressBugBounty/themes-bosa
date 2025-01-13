<header id="masthead" class="site-header header-eleven">
	<div class="top-header">
		<?php if( !get_theme_mod( 'disable_top_header_section', false ) ){ ?>
			<?php if( ( !get_theme_mod( 'disable_contact_detail', false ) && ( !empty(get_theme_mod( 'contact_phone', '' ) ) || !empty(get_theme_mod( 'contact_email', '' ) ) || !empty(get_theme_mod( 'contact_address', '' ) ) ) ) || has_nav_menu( 'menu-3') ){ ?>
				<div class="top-header-inner">
					<div class="container">
						<div class="row align-items-center">
							<div class="col-lg-7 d-none d-lg-block">
								<?php get_template_part( 'template-parts/header', 'contact' ); ?>
							</div>
							<div class="col-lg-5 d-none d-lg-block text-right">
								<?php if( has_nav_menu( 'menu-3') ){ ?>
									<nav class="header-navigation">
										<?php
										wp_nav_menu( array(
											'theme_location' => 'menu-3',
											'menu_id'        => 'secondary-menu',
										) );
										?>
									</nav><!-- #site-navigation -->
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
		<?php if( !get_theme_mod( 'disable_mobile_top_header', false ) ){ ?>
			<?php if( ( !get_theme_mod( 'disable_contact_detail', false ) && ( !empty(get_theme_mod( 'contact_phone', '' ) ) || !empty(get_theme_mod( 'contact_email', '' ) ) || !empty(get_theme_mod( 'contact_address', '' ) ) ) ) || has_nav_menu( 'menu-3') || ( !get_theme_mod( 'disable_header_social_links', false ) && bosa_has_social() ) || is_active_sidebar( 'menu-sidebar' ) ){ ?>
				<div class="alt-menu-icon d-lg-none">
					<a class="offcanvas-menu-toggler" href="#">
						<span class="icon-bar-wrap">
							<span class="icon-bar"></span>
						</span>
						<span class="iconbar-label d-lg-none"><?php echo esc_html( get_theme_mod( 'top_bar_name', 'TOP MENU' ) ); ?></span>
					</a>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
	<div class="mid-header header-image-wrap">
		<?php if( bosa_has_header_media() ){ bosa_header_media(); } ?>
		<div class="container">
			<div class="row align-items-center">
				<?php
				$site_branding_class = 'col-6';
				if( ( get_theme_mod( 'disable_mobile_woocommerce_compare', false ) || get_theme_mod( 'disable_woocommerce_compare', false ) ) && ( get_theme_mod( 'disable_mobile_woocommerce_wishlist', false ) || get_theme_mod( 'disable_woocommerce_wishlist', false ) ) && ( get_theme_mod( 'disable_mobile_woocommerce_account', false ) || get_theme_mod( 'disable_woocommerce_account', false ) ) && ( get_theme_mod( 'disable_mobile_woocommerce_cart', false ) || get_theme_mod( 'disable_woocommerce_cart', false ) ) ){
					$site_branding_class = 'col-12 text-center';
				}
				?>
				<div class="<?php echo esc_attr( $site_branding_class ); ?> col-md-3">
					<?php get_template_part( 'template-parts/site', 'branding' ); ?>
					<div id="slicknav-mobile" class="d-block d-lg-none"></div>
				</div>
				<div class="col-md-6 d-none d-md-block">
				    <?php if ( class_exists('WooCommerce' ) ) {
				    	if( !get_theme_mod( 'disable_search_icon', false ) ){
				    	?>
	    	            <form class="header-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
	    	                <input type="hidden" name="post_type" value="product" />
	    	                <input class="header-search-input" name="s" type="text" placeholder="<?php esc_attr_e('Search products...', 'bosa'); ?>"/>
	    	                <div class="d-inline-block"> 
	    	                	<select class="header-search-select" name="product_cat">
		    	                    <option value=""><?php esc_html_e('All Categories', 'bosa'); ?></option> 
		    	                    <?php
		    	                    $categories = get_categories('taxonomy=product_cat');
		    	                    foreach ($categories as $category) {
		    	                        $option = '<option value="' . esc_attr($category->category_nicename) . '">';
		    	                        $option .= esc_html($category->cat_name);
		    	                        $option .= ' (' . absint($category->category_count) . ')';
		    	                        $option .= '</option>';
		    	                        echo $option; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		    	                    }
		    	                    ?>
		    	                </select>
	    	                </div>
	    	                <button class="header-search-button" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
	    	            </form>
			    	<?php }
			    } ?>
				</div>
				<div class="col-6 col-md-3">
					<?php if ( class_exists('WooCommerce' ) ) { ?>
					    <div class="header-right hidden-xs" >
					        <?php
					        if( !get_theme_mod( 'disable_woocommerce_compare', false ) ){bosa_head_compare();
					        }
					        if( !get_theme_mod( 'disable_woocommerce_wishlist', false ) ){bosa_head_wishlist();
					        }
					        if( !get_theme_mod( 'disable_woocommerce_account', false ) ){bosa_my_account();
					        }
					        if( !get_theme_mod( 'disable_woocommerce_cart', false ) ){bosa_header_cart();
					        }
					        ?>
					    </div>	
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="overlay"></div>
	</div>
	<div class="bottom-header fixed-header">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-9 d-none d-lg-block">
					<nav id="site-navigation" class="main-navigation d-none d-lg-flex">
						<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'bosa' ); ?></button>
						<?php if ( has_nav_menu( 'menu-1' ) ) :
							wp_nav_menu( 
								array(
									'container'      => '',
									'theme_location' => 'menu-1',
									'menu_id'        => 'primary-menu',
									'menu_class'     => 'menu nav-menu',
								)
							);
						?>
						<?php else :
							wp_page_menu(
								array(
									'menu_class' => 'menu-wrap',
									'before'     => '<ul id="primary-menu" class="menu nav-menu">',
									'after'      => '</ul>',
								)
							);
						?>
						<?php endif; ?>
					</nav><!-- #site-navigation -->	
				</div>
				<div class="col-lg-3 d-none d-lg-block">
					<div class="header-icons">
						<?php if( !get_theme_mod( 'disable_header_social_links', false ) && bosa_has_social() ){
							echo '<div class="social-profile">';
								bosa_social();
							echo '</div>'; 
						} ?>
						<?php if( !get_theme_mod( 'disable_hamburger_menu_icon', false ) && is_active_sidebar( 'menu-sidebar' ) ){ ?>
							<div class="alt-menu-icon d-none d-lg-inline-block">
								<a class="offcanvas-menu-toggler" href="#">
									<span class="icon-bar"></span>
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>	
		<!-- header search form end-->
		<div class="mobile-menu-container"></div>
	</div>
	<?php get_template_part( 'template-parts/offcanvas', 'menu' ); ?>
</header><!-- #masthead -->