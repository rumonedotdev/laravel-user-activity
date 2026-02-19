<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Activity</title>

    {{ Haruncpi\LaravelUserActivity\LaravelUserActivity::css()  }}
    {{ Haruncpi\LaravelUserActivity\LaravelUserActivity::js()  }}

</head>
<body>
<script>
    {{--window.__USER_ACTIVITY_BOOT__ = @json([--}}
    {{--    'routePath' => url(config('user-activity.route_path')),--}}
    {{--    'adminPanelPath' => url(config('user-activity.admin_panel_path')),--}}
    {{--    'deleteLimit' => config('user-activity.delete_limit'),--}}
    {{--    'tables' => array_values($tables),--}}
    {{--    'csrfToken' => csrf_token(),--}}
    {{--]);--}}
</script>
<div id="app"></div>
</body>
</html>
