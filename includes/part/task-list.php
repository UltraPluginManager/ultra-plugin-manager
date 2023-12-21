
<div class="app-engage ">
	<!--begin::Prebuilts toggle-->
	<a class="app-engage-btn hover-dark" id="kt_drawer_example_permanent_toggle" style="cursor: pointer;">
		<i class="ki-duotone ki-abstract-41 fs-1 pt-1 mb-2"><span class="path1"></span><span class="path2"></span></i>
		Task List
	</a>
	<!--end::Prebuilts toggle-->
</div>
<button class="btn btn-primary me-3" data-kt-drawer-toggle="#kt_drawer_example_permanent_toggle" id="kt_drawer_example_permanent_toggler" hidden></button>
<!--begin::Drawer-->
<div
    id="kt_drawer_example_permanent"
    class="bg-white"
    data-kt-drawer="true"
    data-kt-drawer-activate="true"
    data-kt-drawer-toggle="#kt_drawer_example_permanent_toggle"
    data-kt-drawer-close="#kt_drawer_example_permanent_close"
    data-kt-drawer-overlay="true"
    data-kt-drawer-permanent="false"
    data-kt-drawer-width="{default:'300px', 'md': '500px'}"
>
    <!--begin::Card-->
    <div class="card rounded-0 w-100">
        <!--begin::Card header-->
        <div class="card-header pe-2">
            <!--begin::Title-->
            <div class="card-title">
				Task Manager
            </div>
            <!--end::Title-->

            <div class="card-toolbar">
                <div class="btn btn-sm btn-icon btn-active-light-primary" id="refreshButton">
					Refresh
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <div class="card-body card-scroll h-200px">
			<div class="table-responsive">
				<table class="table table-striped gy-3 gs-3" id="table-task-list-data">
					<thead>
						<tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
							<th>Name</th>
							<th>Install</th>
							<th>Activate</th>
							<th>Delete</th>
						</tr>
					</thead>
				</table>
			</div>
        </div>
        <!--end::Card body-->
		<div class="card-footer d-flex justify-content-end">
			<button type="button" class="btn btn-danger" onclick="ultrapm_clearTaskList()" style="margin-right: 10px;">Clear Task</button>
			<button type="button" class="btn btn-primary" onclick="ultrapm_installTaskList()" style="margin-right: 10px;">Run Task</button>
			<button type="button" class="btn btn-light" id="kt_drawer_example_permanent_close">Close</button>
		</div>
    </div>
    <!--end::Card-->
</div>
<!--end::Drawer-->

    