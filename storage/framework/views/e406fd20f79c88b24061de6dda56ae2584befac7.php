<?php $__env->startSection('title'); ?>
Admin - <?php echo e(__('crud.navlinks.dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
<?php echo e(__('crud.navlinks.dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('startScripts'); ?>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" defer></script>
<script src="<?php echo e(asset('js/charts/charts-lines.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/charts/charts-pie.js')); ?>" defer></script>
<script src="https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js"></script>

<link rel="stylesheet" href="<?php echo e(asset('css/jquery-jvectormap.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Cards -->
<div class="grid gap-6 mb-0 md:grid-cols-2 xl:grid-cols-4">
    <!-- Card -->
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div class="p-3 mr-4 text-purple-500 text- bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
            <i class="fa p-2 fa-2x fa-users"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                Total clients
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($user_count); ?>

            </p>
        </div>
    </div>
    <!-- Card -->
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
            <i class="fa p-2 fa-2x fa-user-secret"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Agents
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($agent_count); ?>

            </p>
        </div>
    </div>
    <!-- Card -->
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
            <i class="fa p-2 fa-2x fa-briefcase"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Provider
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($provider_count); ?>

            </p>
        </div>
    </div>
    <!-- Card -->
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div
            class="p-3 mr-4 text-teal-500 rounded-full bg-yellow-100 text-yellow-500 dark:bg-yellow-500 dark:text-yellow-100">
            <i class="fa p-2 fa-2x fa-gavel"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Disputes
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($dispute_count); ?>

            </p>
        </div>
    </div>
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div
            class="p-3 mr-4 text-teal-500 rounded-full bg-purple-100 text-purple-500 dark:bg-purple-500 dark:text-purple-100">
            <i class="fa p-2 fa-2x fa-motorcycle"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                <?php echo e(__('crud.general.total')); ?> <?php echo e(__('crud.general.rides')); ?>

            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($rides_count); ?>

            </p>
        </div>
    </div>
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div
            class="p-3 mr-4 text-teal-500 rounded-full bg-green-100 text-green-500 dark:bg-green-500 dark:text-green-100">
            <i class="fa p-2 fa-2x fa-usd"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                <?php echo e(__('crud.general.total')); ?> <?php echo e(__('crud.general.earning')); ?>

            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e(currency($revenue)); ?>

            </p>
        </div>
    </div>
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div
            class="p-3 mr-4 text-teal-500 rounded-full bg-yellow-100 text-yellow-500 dark:bg-yellow-500 dark:text-yellow-100">
            <i class="fa p-2 fa-2x fa-times"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                <?php echo app('translator')->get('crud.general.cancelled_rides'); ?>
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($cancelled_rides); ?>

            </p>
        </div>
    </div>
    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div
            class="p-3 mr-4 text-teal-500 rounded-full bg-blue-100 text-blue-500 dark:bg-blue-500 dark:text-blue-100">
            <i class="fa p-2 fa-2x fa-check"></i>
        </div>
        <div>
            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                Completed Rides
            </p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                <?php echo e($completed_rides); ?>

            </p>
        </div>
    </div>
</div>

<!-- User Map -->
<h2 class="mt-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
    Service Request Map
</h2>
<div class="w-full overflow-hidden rounded-lg shadow-xs mt-6">
    <div class="w-full overflow-x-auto">
        <div id="widgetJvmap" style="height: 500px"></div>
    </div>
</div>

<!-- Charts -->
<h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
    Charts
</h2>
<div class="grid gap-6 mb-0 md:grid-cols-2">
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Revenue</h4>
        <canvas id="pie"></canvas>
    </div>
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Admin Credits</h4>
        <canvas id="line"></canvas>
    </div>
</div>

<!-- Charts -->
<h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
    Recent User Requests
</h2>
<!-- New Table -->
<div class="w-full overflow-hidden mb-8 rounded-lg shadow-xs">
    <div class="w-full overflow-x-auto">
        <table class="w-full whitespace-no-wrap">
            <thead>
                <tr
                    class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                    <th class="px-4 py-3"><?php echo e(__('crud.inputs.SNo')); ?></th>
                    <th class="px-4 py-3"><?php echo e(__('crud.admin.users.name')); ?></th>
                    <th class="px-4 py-3"><?php echo e(__('crud.inputs.amount')); ?></th>
                    <th class="px-4 py-3"><?php echo e(__('crud.inputs.status')); ?></th>
                    <th class="px-4 py-3"><?php echo e(__('crud.inputs.date')); ?></th>
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.general.actions')); ?></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="text-gray-700 dark:text-gray-400">
                    <td class="px-4 py-3"><?php echo e($loop->index+1); ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                            <!-- Avatar with inset shadow -->
                            <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                <img class="object-cover w-full h-full rounded-full"
                                    src="<?php echo e($request->user->avatar ? asset('storage/'.$request->user->avatar) : asset('img/avatar.png')); ?>"
                                    alt="" loading="lazy" />
                                <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-400"><?php echo e($request->user->name); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-400">
                        <?php echo e(currency($request->payment->total ?? 0)); ?>

                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 font-semibold leading-tight rounded-full">
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.request-show-status','data' => ['status' => $request->status]]); ?>
<?php $component->withName('request-show-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->status)]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <?php echo e($request->created_at->toDate()->format('d/m/Y')); ?>

                        <?php echo e($request->created_at->format('h:i:s a')); ?>

                    </td>
                    <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3 text-sm flex justify-center">
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.buttons.show','data' => ['link' => route('admin.request.detail', $request)]]); ?>
<?php $component->withName('buttons.show'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.request.detail', $request))]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td class="px-4 py-3 text-sm text-center" colspan="8">
                        <?php echo e(__('crud.general.not_found')); ?>

                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('endScripts'); ?>
<script src="<?php echo e(asset('js/jquery-jvectormap.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/maps/jquery-jvectormap-world-mill-en.js')); ?>"></script>
<script>
    $(function(){
            var plants = [
                <?php echo e($req); ?>

            ];

            new jvm.Map({
                container: $('#widgetJvmap'),
                map: 'world_mill',
                markers: plants.map(function(h){ return {name: h.name, latLng: h.coords} }),
                markerStyle: {
                    initial: {
                        r: 8,
                        fill: 'purple',
                        stroke: '#383f47'
                    },
                    hover: {
                        r: 12,
                        stroke: ("purple"),
                        'stroke-width': 0
                    }
                },
                labels: {
                    markers: {
                        render: function(index){
                            return '';
                        },
                        offsets: function(index){
                            var offset = plants[index]['offsets'] || [0, 0];
                            return [offset[0] - 7, offset[1] + 3];
                        },
                    }
                },
                series: {
                    regions: [{
                        scale: ['#DEEBF7', '#08519C'],
                        attribute: 'fill',
                    }]
                }
            });
        });
</script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>