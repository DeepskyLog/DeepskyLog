<nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">DeepskyLog</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <?php echo $__env->make('layout.header.view', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            <?php echo $__env->make('layout.header.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            <?php echo $__env->make('layout.header.add', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            @hasrole('admin')
                <?php echo $__env->make('layout.header.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            @endhasrole()
            <?php echo $__env->make('layout.header.downloads', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            <?php echo $__env->make('layout.header.help', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
        </ul>

        <ul class="navbar-nav">
            <button class="btn btn-light fas fa-adjust" id="nightMode" style="margin-right:5px;border:0;" alt="Night Mode"></button>

            <?php if(Auth::guest()): ?>
                <?php echo $__env->make('layout.header.register', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
            <?php else: ?>
                <?php echo $__env->make('layout.header.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;

                <button class="btn" style="margin-right:5px;border:0;">
                    <a href="/message/view">
                        <span style="color: #FFFFFF" class="fas fa-inbox"></span>&nbsp;
                        <span class="badge badge-pill badge-secondary">4</span>
                    </a>
                </button>
            <?php endif; ?>
        </ul>
</div>
</nav>
