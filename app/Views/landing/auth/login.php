
        <!-- Hero Start -->
        <section class="bg-home d-flex align-items-center" style="background: url('images/authentication.jpg') center center;">
            <div class="bg-overlay bg-overlay-primary"></div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-7">
                        <div class="login_page bg-white rounded p-4">
                            <div class="text-center">
                                <h4 class="mb-3">Login / Signin</h4>
                            </div>
                            <?php echo validation_errors(); ?>
                            <form class="login-form" method="post" accept-charset="utf-8" action="<?php echo base_url('auth/user_login'); ?>">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label">Your Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" placeholder="Email" name="prj_lg_eml" required="">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="prj_lg_pwd" placeholder="Password" required="">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <p class="float-end position-relative" style="z-index: 99;"><a href="<?php echo base_url('auth/forgot') ;?>" class="text-dark fw-bold">Forgot password ?</a></p>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                                <label class="form-check-label" for="flexCheckDefault">Remember me</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12 mb-0">
                                        <button class="btn btn-primary w-100">Sign in</button>
                                    </div>
                                    <!--end col-->
                                    <div class="col-12 text-center">
                                        <p class="mb-0 mt-4"><small class="text-dark me-2">Don't have an account ?</small> <a href="<?php echo base_url('auth/register') ;?>" class="text-dark fw-bold">Sign Up</a></p>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end container-->
        </section>
        <!--end section-->
        <!-- Hero End -->