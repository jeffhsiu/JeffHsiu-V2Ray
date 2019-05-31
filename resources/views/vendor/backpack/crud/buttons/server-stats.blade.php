<button class="btn btn-xs btn-default stats-btn" data-server-id="{{ $entry->id }}">
    <i class="fa fa-safari"></i> Stats
</button>

<script>
    $(document).ready(function() {
        $('.stats-btn').click(function (e) {
            e.preventDefault();
            var server_id = $(this).data('server-id')
            $('.stats-btn').attr('disabled', true);
            window.location.href = "{{ backpack_url('vps/server/stats/') }}"+'/'+server_id;
        })
    });
</script>