<?php





use Router as Route;









Route::get('/login', 'AuthController@showLoginForm');
Route::post('/login', 'AuthController@login');
Route::get('/logout', 'AuthController@logout');
Route::get('/forgot-password', 'AuthController@showForgotPassword');
Route::post('/forgot-password', 'AuthController@sendResetLink');
Route::post('/forgot-password/verify-otp', 'AuthController@verifyResetOtp');
Route::post('/forgot-password/complete', 'AuthController@completeResetPassword');
Route::get('/captcha', 'AuthController@generateCaptcha');


Route::get('/register', 'RegisterController@showRegisterForm');
Route::post('/register/send-otp', 'RegisterController@sendOtp');
Route::post('/register/verify-otp', 'RegisterController@verifyOtp');
Route::post('/register/complete', 'RegisterController@complete');




Route::get('/auth/{platform}/callback', 'SocialAuthController@callback');



Route::get('/social-media', 'SocialMediaController@index', ['Auth']);
Route::get('/social-media/{platform}/connect', 'SocialMediaController@connect', ['Auth']);
Route::get('/social-media/{platform}/callback', 'SocialMediaController@callback');
Route::post('/social-media/{platform}/disconnect', 'SocialMediaController@disconnect', ['Auth']);
Route::post('/social-media/{platform}/reconnect', 'SocialMediaController@reconnect', ['Auth']);






Route::get('/', 'AuthController@showLoginForm');
Route::get('/dashboard', 'DashboardController@index', ['Auth']);


Route::get('/planning', 'PlanningController@index', ['Auth']);
Route::get('/planning/create', 'PlanningController@create', ['Auth']);
Route::post('/planning/store', 'PlanningController@store', ['Auth']);
Route::get('/planning/{id}', 'PlanningController@show', ['Auth']);
Route::get('/planning/{id}/edit', 'PlanningController@edit', ['Auth']);
Route::post('/planning/{id}/update', 'PlanningController@update', ['Auth']);
Route::post('/planning/{id}/delete', 'PlanningController@delete', ['Auth']);
Route::post('/planning/{id}/status', 'PlanningController@updateStatus', ['Auth']);
Route::post('/planning/bulk-action', 'PlanningController@bulkAction', ['Auth']);


Route::get('/calendar', 'CalendarController@index', ['Auth']);
Route::get('/calendar/data', 'CalendarController@getData', ['Auth']);
Route::post('/calendar/drag-drop', 'CalendarController@dragDrop', ['Auth']);
Route::post('/calendar/note/store', 'CalendarController@storeNote', ['Auth']);
Route::post('/calendar/note/update', 'CalendarController@updateNote', ['Auth']);
Route::post('/calendar/note/delete', 'CalendarController@deleteNote', ['Auth']);


Route::get('/approval', 'ApprovalController@taskIndex', ['Auth']);
Route::get('/approval/{id}', 'ApprovalController@taskShow', ['Auth']);
Route::post('/approval/{id}/task-approve', 'ApprovalController@taskApprove', ['Auth']);
Route::post('/approval/{id}/task-revision', 'ApprovalController@taskRevision', ['Auth']);
Route::post('/approval/{id}/approve', 'ApprovalController@approve', ['Auth']);
Route::post('/approval/{id}/reject', 'ApprovalController@reject', ['Auth']);
Route::post('/approval/{id}/revision', 'ApprovalController@requestRevision', ['Auth']);
Route::post('/approval/{id}/recheck', 'ApprovalController@recheck', ['Auth', 'SuperAdmin']);
Route::get('/archive', 'ApprovalController@archive', ['Auth']);
Route::post('/archive/delete-month', 'ApprovalController@archiveDeleteMonth', ['Auth']);
Route::post('/archive/delete-task', 'ApprovalController@archiveDeleteTask', ['Auth']);
Route::get('/archive/{id}', 'ApprovalController@archiveShow', ['Auth']);


Route::get('/scheduler', 'SchedulerController@index', ['Auth']);
Route::get('/scheduler/queue', 'SchedulerController@queue', ['Auth']);
Route::post('/scheduler/run-now', 'SchedulerController@runNow', ['Auth']);
Route::post('/scheduler/cancel/{id}', 'SchedulerController@cancel', ['Auth']);
Route::post('/scheduler/upload-task', 'SchedulerController@uploadTask', ['Auth']);
Route::post('/scheduler/update-schedule', 'SchedulerController@updateSchedule', ['Auth']);


Route::get('/posting', 'PostingController@index', ['Auth']);
Route::get('/posting/logs', 'PostingController@logs', ['Auth']);
Route::get('/posting/{id}', 'PostingController@show', ['Auth']);
Route::post('/planning/{id}/publish-now', 'PostingController@publishNow', ['Auth']);


Route::get('/draft', 'DraftController@index', ['Auth']);
Route::get('/draft/create', 'DraftController@create', ['Auth']);
Route::post('/draft/store', 'DraftController@store', ['Auth']);
Route::get('/draft/{id}/edit', 'DraftController@edit', ['Auth']);
Route::post('/draft/{id}/update', 'DraftController@update', ['Auth']);
Route::post('/draft/{id}/submit', 'DraftController@submitReview', ['Auth']);


Route::get('/analytics', 'AnalyticsController@index', ['Auth']);
Route::get('/analytics/data', 'AnalyticsController@getData', ['Auth']);
Route::get('/analytics/export/{type}', 'AnalyticsController@export', ['Auth']);



Route::get('/master/program', 'MasterController@program', ['Auth']);
Route::post('/master/program/store', 'MasterController@programStore', ['Auth']);
Route::post('/master/program/{id}/update', 'MasterController@programUpdate', ['Auth']);
Route::post('/master/program/{id}/delete', 'MasterController@programDelete', ['Auth']);


Route::get('/master/kategori', 'MasterController@kategori', ['Auth']);
Route::post('/master/kategori/store', 'MasterController@kategoriStore', ['Auth']);
Route::post('/master/kategori/{id}/update', 'MasterController@kategoriUpdate', ['Auth']);
Route::post('/master/kategori/{id}/delete', 'MasterController@kategoriDelete', ['Auth']);


Route::get('/master/platform', 'MasterController@platform', ['Auth']);
Route::post('/master/platform/{id}/toggle', 'MasterController@platformToggle', ['Auth']);


Route::get('/master/tags', 'MasterController@tags', ['Auth']);
Route::post('/master/tags/store', 'MasterController@tagsStore', ['Auth']);
Route::post('/master/tags/{id}/delete', 'MasterController@tagsDelete', ['Auth']);


Route::get('/master/hashtag', 'MasterController@hashtag', ['Auth']);
Route::post('/master/hashtag/store', 'MasterController@hashtagStore', ['Auth']);
Route::post('/master/hashtag/{id}/delete', 'MasterController@hashtagDelete', ['Auth']);


Route::get('/master/template', 'MasterController@template', ['Auth']);
Route::post('/master/template/store', 'MasterController@templateStore', ['Auth']);
Route::post('/master/template/{id}/update', 'MasterController@templateUpdate', ['Auth']);
Route::post('/master/template/{id}/delete', 'MasterController@templateDelete', ['Auth']);


Route::get('/master/lokasi', 'MasterController@lokasi', ['Auth']);
Route::post('/master/lokasi/store', 'MasterController@lokasiStore', ['Auth']);
Route::post('/master/lokasi/{id}/update', 'MasterController@lokasiUpdate', ['Auth']);
Route::post('/master/lokasi/{id}/delete', 'MasterController@lokasiDelete', ['Auth']);


Route::get('/master/talent', 'MasterController@talent', ['Auth']);
Route::post('/master/talent/store', 'MasterController@talentStore', ['Auth']);
Route::post('/master/talent/{id}/update', 'MasterController@talentUpdate', ['Auth']);
Route::post('/master/talent/{id}/delete', 'MasterController@talentDelete', ['Auth']);


Route::get('/intern-users', 'InternUserController@index', ['Auth']);
Route::post('/intern-users/{id}/approve', 'InternUserController@approve', ['Auth']);
Route::post('/intern-users/{id}/reject', 'InternUserController@reject', ['Auth']);
Route::post('/intern-users/{id}/delete', 'InternUserController@delete', ['Auth']);


Route::get('/roles', 'RoleController@index', ['Auth', 'SuperAdmin']);


Route::get('/platform-accounts', 'PlatformAccountController@index', ['Auth']);
Route::get('/platform-accounts/connect/{platform}', 'PlatformAccountController@connect', ['Auth']);
Route::post('/platform-accounts/{id}/disconnect', 'PlatformAccountController@disconnect', ['Auth']);
Route::post('/platform-accounts/{id}/refresh-token', 'PlatformAccountController@refreshToken', ['Auth']);
Route::get('/platform-accounts/{id}/sync', 'PlatformAccountController@sync', ['Auth']);


Route::get('/activity-logs', 'ActivityLogController@index', ['Auth', 'SuperAdmin']);


Route::get('/settings', 'SettingsController@index', ['Auth', 'SuperAdmin']);
Route::post('/settings/update', 'SettingsController@update', ['Auth', 'SuperAdmin']);
Route::post('/settings/backup', 'SettingsController@backup', ['Auth', 'SuperAdmin']);
Route::get('/settings/backup/download/{id}', 'SettingsController@downloadBackup', ['Auth', 'SuperAdmin']);
Route::post('/settings/restore', 'SettingsController@restore', ['Auth', 'SuperAdmin']);


Route::get('/profile', 'ProfileController@index', ['Auth']);
Route::post('/profile/update', 'ProfileController@update', ['Auth']);
Route::post('/profile/change-password', 'ProfileController@changePassword', ['Auth']);
Route::post('/profile/update-avatar', 'ProfileController@updateAvatar', ['Auth']);




Route::get('/api/planning/stats', 'ApiController@planningStats', ['Auth']);
Route::get('/api/planning/search', 'ApiController@searchPlanning', ['Auth']);
Route::get('/api/planning/calendar', 'ApiController@calendarData', ['Auth']);
Route::get('/api/dashboard/stats', 'ApiController@dashboardStats', ['Auth']);
Route::get('/api/dashboard/chart', 'ApiController@dashboardChart', ['Auth']);
Route::get('/api/analytics/data', 'ApiController@analyticsData', ['Auth']);
Route::get('/api/notifications', 'ApiController@notifications', ['Auth']);
Route::get('/api/notifications/unread-count', 'ApiController@notificationsUnreadCount', ['Auth']);
Route::get('/api/notifications/pegawai', 'ApiController@pegawaiNotifications', ['Auth']);
Route::post('/api/notifications/read', 'ApiController@markNotificationsRead', ['Auth']);
Route::post('/api/notifications/mark-all-read', 'ApiController@markAllNotificationsRead', ['Auth']);
Route::post('/api/notifications/mark-multiple-read', 'ApiController@markMultipleNotificationsRead', ['Auth']);
Route::get('/api/master/{table}', 'ApiController@masterData', ['Auth']);




Route::get('/import', 'ImportController@index', ['Auth']);
Route::post('/import/upload', 'ImportController@upload', ['Auth']);
Route::get('/import/template', 'ImportController@downloadTemplate', ['Auth']);
Route::get('/export/planning', 'ImportController@export', ['Auth']);

Route::get('/timeline', 'TimelineController@index', ['Auth']);
Route::get('/timeline/get/{id}', 'TimelineController@get', ['Auth']);
Route::get('/timeline/get-planning/{id}', 'TimelineController@getPlanning', ['Auth']);
Route::get('/timeline/get-by-range', 'TimelineController@getByRange', ['Auth']);
Route::get('/timeline/get-calendar', 'TimelineController@getCalendar', ['Auth']);
Route::post('/timeline/store', 'TimelineController@store', ['Auth']);
Route::post('/timeline/update/{id}', 'TimelineController@update', ['Auth']);
Route::post('/timeline/delete/{id}', 'TimelineController@delete', ['Auth']);
Route::post('/timeline/update-status', 'TimelineController@updateStatus', ['Auth']);
Route::post('/timeline/submit-approval', 'TimelineController@submitApproval', ['Auth']);
Route::post('/timeline/save-calendar', 'TimelineController@saveCalendar', ['Auth']);
Route::post('/timeline/generate-calendar', 'TimelineController@generateCalendar', ['Auth']);
Route::post('/timeline/upload-file', 'TimelineController@uploadFile', ['Auth']);




Route::post('/auto-post/process', 'SchedulerController@processQueue', ['Auth']);
Route::get('/auto-post/status', 'SchedulerController@autoPostStatus', ['Auth']);
Route::post('/auto-post/retry/{id}', 'SchedulerController@retryAutoPost', ['Auth']);




Route::post('/upload/file', 'UploadController@uploadFile', ['Auth']);
Route::post('/upload/image', 'UploadController@uploadImage', ['Auth']);
Route::post('/upload/video', 'UploadController@uploadVideo', ['Auth']);
Route::post('/upload/thumbnail', 'UploadController@uploadThumbnail', ['Auth']);




Route::get('/cron/scheduler', 'CronController@scheduler');
Route::get('/cron/refresh-tokens', 'CronController@refreshTokens');
Route::get('/cron/cleanup-logs', 'CronController@cleanupLogs');
Route::get('/cron/backup', 'CronController@backup');






