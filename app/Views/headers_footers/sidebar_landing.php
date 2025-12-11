

                <div id="navigation">
                    <!-- Navigation Menu-->   
                    <ul class="navigation-menu">
						<?php
							if ($pag == 'landing') {
								echo '<li class="active">';
							}else{
								echo '<li>';
						}?>
							<a href="<?php echo base_url('landing'); ?>" class="sub-menu-item">Home</a>
						<?php echo '</li>'; ?>

						<?php
							if ($pag == 'download') {
								echo '<li class="active">';
							}else{
								echo '<li>';
						}?>
							<a href="<?php echo base_url('download'); ?>" class="sub-menu-item">Download</a>
						<?php echo '</li>'; ?>

						<?php
							if ($pag == 'about') {
								echo '<li class="active">';
							}else{
								echo '<li>';
						}?>
							<a href="<?php echo base_url('aboutus'); ?>" class="sub-menu-item">About Us</a>
						<?php echo '</li>'; ?>

						<?php
							if ($pag == 'contact') {
								echo '<li class="active">';
							}else{
								echo '<li>';
						}?>
							<a href="<?php echo base_url('contactus'); ?>" class="sub-menu-item">Contact Us</a>
						<?php echo '</li>'; ?>

						<?php
							if ($pag == 'faqs_terms') {
								echo '<li class="active">';
							}else{
								echo '<li>';
						}?>
							<a href="<?php echo base_url('faqs_terms'); ?>" class="sub-menu-item">FAQs</a>
						<?php echo '</li>'; ?>

						<li class="">
							<div class="buy-button">
								<a href="<?php echo base_url('/login'); ?>" target="_blank" class="btn btn-primary">Login</a>
							</div>
						</li>'

                    </ul>
                    <!--end navigation menu-->
                </div>
                <!--end navigation-->
            </div>
            <!--end container-->
        </header>
        <!--end header-->
        <!-- Navbar End -->
        <!-- Hero Start -->
       