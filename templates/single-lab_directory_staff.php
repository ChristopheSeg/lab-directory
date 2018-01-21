<?php get_header(); ?>

            <?php
                $published = get_post_status( $post->$ID ) == 'published' ? '.published' : '';
            ?>
<style type="text/css">

  .single-lab_directory_staff {
    padding: 0px 10px;
  }
</style>

			<div id="content">


				<div id="inner-content" class="wrap cf">
					<main id="main" class="faculty-main <?php echo $published; ?>" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<?php if (have_posts()) :?>

                            <article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">

 
                              <div class="faculty-profile-info entry-content <?php echo $published; ?>">
                                  SINGLE-l_d_staff.php
                                 <?php if (has_post_thumbnail( $post->ID ) ){
                                          $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
                                          ?>
                                              <img src="<?php echo $image[0]; ?>" alt="profile image">
                                          <?php
                                      } else {
                                          //Do nothing
                                      }
                                  ?>
                                  <?php //All shortcodes simply return the appropriate strings, so to print them all without the lab_directory_staff loop we have to echo do_shortcode() ?>
                                  <div class="lab-directory-profile-info <?php echo $published; ?>">
                                          <div class="single-lab_directory_staff">
                                              <?php if(do_shortcode("[ld_name]")): ?>
                                                  <div class="name" title="Name">
                                                      <i class="fa fa-user" aria-hidden="true"></i>
                                                      <?php echo do_shortcode("[ld_name]"); ?>
                                                  </div>
                                              <?php endif; ?>
                                              <?php if(do_shortcode("[ld_position]")): ?>
                                                  <div class="position" title="Position">
                                                      <i class="fa fa-briefcase" aria-hidden="true"></i>
                                                      <?php echo do_shortcode("[position]"); ?>
                                                  </div>
                                              <?php endif; ?>
                                              <?php if(do_shortcode("[ld_email]")): ?>
                                                  <div class="email" title="E-mail address">
                                                      <i class="fa fa-envelope" aria-hidden="true"></i>
                                                      <?php echo do_shortcode("[email]"); ?>
                                                  </div>
                                              <?php endif; ?>
                                              <?php if(do_shortcode("[ld_phone_number]")): ?>
                                                  <div class="phone" title="Phone number">
                                                      <i class="fa fa-phone" aria-hidden="true"></i>
                                                      <?php echo do_shortcode("[phone_number]"); ?>
                                                  </div>
                                              <?php endif; ?>
                                          </div>
                                  </div>

                              </div>

                              <section class="faculty-profile-content entry-content <?php echo $published; ?>" itemprop="articleBody">
                                <?php
                                    // replace by bio_shorcode !! 
                                    $content = get_post_meta ($post->ID , 'bio', true); // get_the_content();

                                    if($content){
                                        echo '<p>' . $content . '</p>';
                                    } else {
                                        echo '<p> No biography found. </p>';
                                    }

                                ?>
                              </section> <?php // end article section ?>

                              <?php //comments_template(); ?>

                            </article> <?php // end article ?>

					

						<?php else : ?>

							<article id="post-not-found" class="hentry cf">
									<header class="article-header">
										<h1>Post not found</h1>
									</header>
									<section class="entry-content">
										<p>The content you are trying to access does not seem to exist.</p>
									</section>
							</article>

						<?php endif; ?>

					</main>

					<?php //get_sidebar(); ?>

				</div>

			</div>

<?php get_footer(); ?>
