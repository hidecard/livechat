<div class="timestamp">
    <?php echo (new DateTime($msg['created_at']))->format('Y-m-d H:i:s'); ?>
    <?php if ($msg['sender_id'] == $user_id): ?>
        <span class="status">
            <?php if ($msg['is_read']): ?>
                ✓✓ Read
            <?php else: ?>
                ✓ Delivered
            <?php endif; ?>
        </span>
    <?php endif; ?>
</div>
