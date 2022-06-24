<?php
importClassWpf('DbWpf');
importClassWpf('InstallerWpf');
importClassWpf('BaseObjectWpf');
importClassWpf('ModuleWpf');
importClassWpf('ModelWpf');
importClassWpf('ViewWpf');
importClassWpf('ControllerWpf');
importClassWpf('HelperWpf');
importClassWpf('DispatcherWpf');
importClassWpf('FieldWpf');
importClassWpf('TableWpf');
importClassWpf('FrameWpf');
/**
 * Deprecated classes
 *
 * @deprecated since version 1.0.1
 */
importClassWpf('LangWpf');
importClassWpf('ReqWpf');
importClassWpf('UriWpf');
importClassWpf('HtmlWpf');
importClassWpf('ResponseWpf');
importClassWpf('FieldAdapterWpf');
importClassWpf('ValidatorWpf');
importClassWpf('ErrorsWpf');
importClassWpf('UtilsWpf');
importClassWpf('ModInstallerWpf');
importClassWpf('InstallerDbUpdaterWpf');
importClassWpf('DateWpf');
/**
 * Check plugin version - maybe we need to update database, and check global errors in request
 */
//InstallerWpf::update();
//ErrorsWpf::init();
/**
 * Start application
 */
FrameWpf::_()->parseRoute();
FrameWpf::_()->init();
FrameWpf::_()->exec();
