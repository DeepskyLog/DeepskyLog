        <thead>
            <tr>
                <th>
                    {{  _i("Subject") }}
                </th>
                <th>
                    {{ _i("Unread messages") }}
                </th>
                <th>
                    {{  _i("Message started by") }}
                </th>
                <th>
                    {{ _i("Latest message by") }}
                </th>
                <th>
                    {{ _i("Latest update") }}
                </th>
                <th style="display:none;">
                    Date
                </th>
            </tr>
        </thead>

        <tbody>
    @foreach ($threads as $thread)
        <?php
            $new = $thread->isUnread(Auth::id()) ? '<span class="badge badge-success">New</span>' : '';
        ?>

        <tr>
            <td>
                <a href="{{ route('messages.show', $thread->id) }}">{!! $new !!} {{ $thread->subject }}</a>
            </td>
            <td>
                {{ $thread->userUnreadMessagesCount(Auth::id()) }}
            </td>
            <td>
                {{ $thread->creator()->name }}
            </td>
            <td>
                {{ $thread->latestMessage->user->name }}
            </td>
            <td>
                {{ $thread->latestMessage->created_at->diffForHumans() }}
            </td>
            <td style="display:none;">
                {{ $thread->latestMessage->created_at }}
            </td>
        </tr>
    @endforeach
        </tbody>
    </table>
