{{ content() }}

<div class="user-profile">
    <table align="center">
        <tr>
            <td class="small remove-image" valign="top">
                <img src="https://secure.gravatar.com/avatar/{{ user.gravatar_id }}?s=64" class="img-rounded">
            </td>
            <td align="left" valign="top">
                <h1>{{ user.name|e }}</h1>

                <p>
                    <span>joined {{ date('M d/Y', user.created_at) }}</span><br>
                </p>

                <ul class="nav nav-tabs">
                    <li class="active"><a href="#">Settings</a></li>
                    <!--<li><a href="#">My themes</a></li>-->
                </ul>

                <p>

                <form method="post">
                    <p>
                        <label for="timezone">Timezone</label>
                        {{ select_static('timezone', timezones) }}
                    </p>

                    <p>
                        <label for="notifications">E-Mail Notifications</label>
                        {{ select_static('notifications', [
                        'N': 'Never',
                        'Y': 'Always',
                        'P': 'Someone replied my posts / Someone has replied in posts that I has replied'
                        ]) }}
                    </p>

                    <p>
                        <a href="https://en.gravatar.com/" target="_self">Change your avatar at Gravatar</a>
                    </p>

                    <p>
                        <input type="submit" class="btn btn-success" value="Save"/>
                    </p>
                </form>
            </td>
        </tr>
    </table>
</div>
