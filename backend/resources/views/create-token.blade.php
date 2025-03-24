<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <form method="POST" action="{{ route('create-token') }}" style="display: flex; flex-direction: column; gap: 15px;">
        @csrf
        <label for="email" style="font-weight: bold; color: #555;">Email</label>
        <input type="email" name="email" id="email" required style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
        <div style="color: red; font-size: 12px;">{{ $error ?? '' }}</div>
        <button type="submit" style="padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background-color 0.3s;">
            Create Token
        </button>
    </form>
    <div style="margin-top: 20px; font-size: 14px; color: #333; text-align: center;">
        Your token: 
        <span id="token" style="font-weight: bold; color: #007bff;">{{ $token ?? '' }}</span>
        <button onclick="copyToken()" style="margin-left: 10px; padding: 8px; background-color: #28a745; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; transition: background-color 0.3s;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="16px" height="16px">
                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
            </svg>
        </button>
    </div>

    <div id="toast" style="position: fixed; top: 20px; right: 20px; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 4px; font-size: 14px; display: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        Token copied to clipboard!
    </div>
</div>

<script>
    function copyToken() {
        const tokenElement = document.getElementById('token');
        const token = tokenElement.textContent;
        navigator.clipboard.writeText(token).then(() => {
            showToast();
        }).catch(err => {
            console.error('Failed to copy token: ', err);
        });
    }

    function showToast() {
        const toast = document.getElementById('toast');
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000); // Toast will disappear after 3 seconds
    }
</script>
