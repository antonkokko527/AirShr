<style>
    .attachment_wrapper_mobile {
        height:216px!important;
    }
</style>

<!-- Edit Map Address Modal -->
<div id="mapModal" class="popover fade">
	<div class="arrow"></div>

	<h3 class = "popover-title">Edit Location<button type="button" class="close" data-dismiss="modal">&times;</button></h3>
	<div class="popover-content">
		<form id="location-form">
			{{--<div class="btn-group" id="location-btns" data-toggle="buttons">--}}
				{{--<label class="btn btn-default" id="single-loc">--}}
					{{--<input type="radio" name="nlocations" value="single" data-toggle="button"> Single--}}
				{{--</label>--}}
				{{--<label class="btn btn-default" id="multiple-loc">--}}
					{{--<input type="radio" name="nlocations" value="multiple" data-toggle="button"> Multiple--}}
				{{--</label>--}}
			{{--</div>--}}
			{{--<div id="mapAddress" style="display:none"></div>--}}
			{{--<div id="locationsUrl" style="display:none"></div>--}}
			<div id="mapAddress" style="display:none"></div>
		</form>
	</div>
</div>

<!-- Edit Action Modal -->
<div id="actionModal" class="popover fade">
	<div class="arrow"></div>
	<h3 class="popover-title">Edit Action<button type="button" class="close" data-dismiss="modal">&times;</button></h3>
	<div class="popover-content">
		<div id="actionType"></div>
		<br/>
		<div id="actionContent"></div>
		<br/>
		<button id="edit_action_submit" type="submit" class="btn btn-primary btn-sm editable-submit"><i class="glyphicon glyphicon-ok"></i></button>
		<button id="edit_action_cancel" type="button" class="btn btn-default btn-sm editable-cancel" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
		<img id="action_loading" src="/img/ajax-loader.gif" style="display:none">
	</div>
</div>
<!-- Image Modal -->
<div id="imageModal" class="popover fade">
	<div class="arrow"></div>
		<h3 class="popover-title">Edit Images<button type="button" class="close" data-dismiss="modal">&times;</button></h3>
		<div class="popover-content" style="width:598px; height:560px">

			<div class="row">
				<div class="col-sm-24">

					<div class="row">


						<div class="col-sm-12">

							<div class="attachment_wrapper same-height dropzone attachment_wrapper_mobile" id="attachment_logo_mobile">
								<div class="attachment-preview nofile">
									<div class="attachment-preview-content">
										<div class="attachment-preview-content-nofile">
											<h3>Logo</h3>
											<h4>Click or drop file</h4>
											<h5>min. 200 * 200</h5>
										</div>
									</div>
								</div>
								<div class="attachment-preview" id="attachment_logo_drop_mobile"></div>
								<div class="attachment-preview file" style="display: none" id="attachment_logo_preview_mobile">
									<div class="attachment-preview-content">
										<img class="attachment-preview-image aspect-fit" />
										<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
										<span class="attachment-filename"></span>
									</div>
								</div>

								<div class="attachment-desc">

								</div>

							</div>

						</div>
						<div class="col-sm-12">

							<div class="attachment_wrapper same-height dropzone attachment_wrapper_mobile" id="attachment_image1_mobile">
								<div class="attachment-preview nofile">
									<div class="attachment-preview-content">
										<div class="attachment-preview-content-nofile">
											<h3>Image 1</h3>
											<h4>Click or drop file</h4>
											<h5>min. 800 * 600</h5>
										</div>
									</div>
								</div>
								<div class="attachment-preview" id="attachment_image1_drop_mobile"></div>
								<div class="attachment-preview file" style="display: none" id="attachment_image1_preview_mobile">
									<div class="attachment-preview-content">
										<img class="attachment-preview-image aspect-fit" />
										<iframe class="attachment-preview-video" width="100%" height="100%"></iframe>
										<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
										<span class="attachment-filename"></span>
									</div>
								</div>

								<div class="attachment-desc">
									<div class="row">
										<div class="col-sm-6">
											<div class="checkbox">
												<label>
													<input type="checkbox" class="video-checkbox" value="">
													Video
												</label>
											</div>
										</div>
										<div class="col-sm-15">
											<input type="text" class="form-control video-textbox" placeholder="Video link" />
										</div>
										<div class="col-sm-3">
											<button type="submit" class="btn btn-primary btn-sm video-submit"><i class="glyphicon glyphicon-ok"></i></button>
										</div>
									</div>

								</div>

							</div>

						</div>


					</div>
				</div>
			</div>

			<div class="row" style="margin-top: 10px">
				<div class="col-sm-24">

					<div class="row">


						<div class="col-sm-12">

							<div class="attachment_wrapper same-height dropzone attachment_wrapper_mobile" id="attachment_image2_mobile">
								<div class="attachment-preview nofile">
									<div class="attachment-preview-content">
										<div class="attachment-preview-content-nofile">
											<h3>Image 2</h3>
											<h4>Click or drop file</h4>
											<h5>min. 800 * 600</h5>
										</div>
									</div>
								</div>
								<div class="attachment-preview" id="attachment_image2_drop_mobile"></div>
								<div class="attachment-preview file" style="display: none" id="attachment_image2_preview_mobile">
									<div class="attachment-preview-content">
										<iframe class="attachment-preview-video" width="100%" height="100%"></iframe>
										<img class="attachment-preview-image aspect-fit" />
										<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
										<span class="attachment-filename"></span>
									</div>
								</div>

								<div class="attachment-desc">
									<div class="row">
										<div class="col-sm-6">
											<div class="checkbox">
												<label>
													<input type="checkbox" class="video-checkbox" value="">
													Video
												</label>
											</div>
										</div>
										<div class="col-sm-15">
											<input type="text" class="form-control video-textbox" placeholder="Video link" />
										</div>
										<div class="col-sm-3">
											<button type="submit" class="btn btn-primary btn-sm video-submit"><i class="glyphicon glyphicon-ok"></i></button>
										</div>
									</div>

								</div>

							</div>

						</div>

						<div class="col-sm-12">

							<div class="attachment_wrapper same-height dropzone attachment_wrapper_mobile" id="attachment_image3_mobile">
								<div class="attachment-preview nofile">
									<div class="attachment-preview-content">
										<div class="attachment-preview-content-nofile">
											<h3>Image 3</h3>
											<h4>Click or drop file</h4>
											<h5>min. 800 * 600</h5>
										</div>
									</div>
								</div>
								<div class="attachment-preview" id="attachment_image3_drop_mobile"></div>
								<div class="attachment-preview file" style="display: none" id="attachment_image3_preview_mobile">
									<div class="attachment-preview-content">
										<iframe class="attachment-preview-video" width="100%" height="100%"></iframe>
										<img class="attachment-preview-image aspect-fit" />
										<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
										<span class="attachment-filename"></span>
									</div>
								</div>

								<div class="attachment-desc">
									<div class="row">
										<div class="col-sm-6">
											<div class="checkbox">
												<label>
													<input type="checkbox" class="video-checkbox" value="">
													Video
												</label>
											</div>
										</div>
										<div class="col-sm-15">
											<input type="text" class="form-control video-textbox" placeholder="Video link" />
										</div>
										<div class="col-sm-3">
											<button type="submit" class="btn btn-primary btn-sm video-submit"><i class="glyphicon glyphicon-ok"></i></button>
										</div>
									</div>

								</div>

							</div>

						</div>

					</div>

				</div>
			</div>
			<div class="modal-footer">
				<hr>
				{{--<button type="button" class="btn btn-primary" id="content_btn_save_mobile">Save</button>--}}
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
</div>

<!-- Image editor modal -->
<div class="content-modal-overlay" id="image_editor_overlay_mobile" style="display: none">

	<div class="content-sub-header">
		<h1 class="content-sub-header-title">Image Editor</h1>
		<div class="content-sub-header-actions">
			<a class="btn-action" title="Confirm" id="content_btn_img_confirm_mobile"><i class="mdi mdi-check"></i></a>
			<a class="btn-action" title="Cancel" id="content_btn_img_cancel_mobile"><i class="mdi mdi-close"></i></a>
		</div>
	</div>

	<div class="container-fluid image-editor-content">
		<div class="image-editor-main-wrapper">
			<div class="image-editor-main-area">
				<div id="image-editor-cropper-div-mobile">
					<img id="image-editor-cropper-img-mobile" />
				</div>
				<div id="image-editor-zoom-container">
					<input id="image-editor-zoom-slider-mobile" class="image-editor-slider" type="text" data-slider-min="10" data-slider-max="300" data-slider-step="10" data-slider-value="100" data-slider-orientation="vertical"/>
				</div>
			</div>

			<p class="success-green image-status-icon"><i class="mdi mdi-checkbox-marked-circle"></i></p>
			<p class="image-status-description success-green">Image resolution is OK</p>
			<p>Uploaded image is <span class="image-information-mobile">900w * 700h (2:1)</span></p>
			<p>Minimum required is 800w * 600h (4:3)</p>

		</div>
		<div class="content-air-preview">
			@include('airshrconnect.mobilepreview', ['mode' => 'image_editor_preview_mobile', 'sliderContainerID' => '', 'displayFormOption' => 'false', 'displayFormCloseOption' => 'false'])
		</div>
	</div>

</div>

<!---->

<div class="modal fade" id="songModal" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="song_modal_title"></h4>
			</div>
			<div class="modal-body">

				<div class="form-group">
					<label for="song_search_artist">Artist</label><input type="text" class="form-control" id="song_search_artist">
				</div>

				<div class="form-group">
					<label for="song_search_title">Title</label><input type="text" class="form-control" id="song_search_title">
				</div>

				<div class="form-group">
					<button type="button" class="btn btn-primary" id="song_modal_button">Search</button>
					<button type="button" id="song_mark_wrong" class="btn btn-danger">Mark Not Available (Song will NOT be displayed to the user)</button>
					<img id="song_modal_loading" src="/img/ajax-loader.gif" style="display:none">
				</div>

				<p>Select the correct song</p>
				<table class="table table-hover">
					<thead>
					<tr>
						<th></th>
						<th>Artist</th>
						<th>Title</th>
					</tr>
					</thead>
					<tbody id="song_table">
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>

<script>
    <?php
        $action_types = \App\ConnectContentAction::orderBy('id', 'desc')->get();
    ?>
    var actionTypesByLabel = {
        @foreach($action_types as $action_type)
            '{{$action_type['action_label']}}' : '{{$action_type['id']}}',
        @endforeach
    };
    var actionTypesByID = {
        @foreach($action_types as $action_type)
            '{{$action_type['id']}}' : '{{$action_type['action_label']}}',
        @endforeach
    };
</script>