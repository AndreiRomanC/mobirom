<link rel="stylesheet" href="css/addUserForm.css">

<div class="form-container">
    <form id="addUserForm">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="permission_level">Permission Level:</label>
            <select name="permission_level" id="permission_level">
                <option value="Admin">Admin</option>
                <option value="User">User</option>
                <option value="Guest">Guest</option>
            </select>
        </div>
        <button type="submit">Add User</button>
    </form>
</div>
