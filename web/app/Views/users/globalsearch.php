<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-search text-primary mr-2"></i>
                            Search Results
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                Query: <b>"<?= esc($query) ?>"</b>
                            </span>
                            <span class="badge badge-info border p-2 ml-2">
                                Found: <b><?= $totalCount ?></b> matches
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Universal search across all data categories</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <nav aria-label="breadcrumb" class="float-right mt-2">
                        <ol class="breadcrumb bg-transparent p-0 mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                            <li class="breadcrumb-item active">Search</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <?php if ($totalCount == 0): ?>
                <div class="alert alert-warning shadow-sm border-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No results found for "<b><?= esc($query) ?></b>". Try searching with different keywords or numbers.
                </div>
            <?php else: ?>

                <!-- Stat Cards Row (small-box style from home page) -->
                <div class="row">
                    <?php foreach ($counts as $key => $count): ?>
                        <?php $tab = $tabs[$key]; ?>
                        <div class="col-lg-2 col-md-3 col-6">
                            <div class="small-box bg-<?= $tab['color'] ?>">
                                <div class="inner">
                                    <h3><?= $count ?></h3>
                                    <p><?= $tab['label'] ?></p>
                                </div>
                                <div class="icon">
                                    <i class="<?= $tab['icon'] ?>"></i>
                                </div>
                                <?php if ($count > 0): ?>
                                    <a href="<?= base_url('globalsearch/' . $key . '?q=' . urlencode($query)) ?>" class="small-box-footer">
                                        View All <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="small-box-footer">
                                        No results <i class="fas fa-arrow-circle-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tab Navigation Pills -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-body py-2">
                                <ul class="nav nav-pills flex-wrap">
                                    <?php foreach ($tabs as $key => $tab): ?>
                                        <?php if ($counts[$key] > 0): ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?= base_url('globalsearch/' . $key . '?q=' . urlencode($query)) ?>">
                                                    <i class="<?= $tab['icon'] ?> mr-1"></i>
                                                    <?= $tab['label'] ?>
                                                    <span class="badge badge-<?= $tab['badge'] ?> ml-1"><?= $counts[$key] ?></span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->