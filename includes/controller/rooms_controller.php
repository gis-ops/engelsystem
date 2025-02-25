<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftsFilterRenderer;

/**
 * Room controllers for managing everything room related.
 */

/**
 * View a room with its shifts.
 *
 * @return array
 */
function room_controller(): array
{
    if (!auth()->can('view_rooms')) {
        throw_redirect(page_link_to());
    }

    $request = request();
    $room = load_room();

    $all_shifts = $room->shifts->sortBy('start');
    $days = [];
    foreach ($all_shifts as $shift) {
        $day = $shift->start->format('Y-m-d');
        if (!in_array($day, $days)) {
            $days[] = $day;
        }
    }

    $shiftsFilter = new ShiftsFilter(
        true,
        [$room->id],
        AngelType::query()->get('id')->pluck('id')->toArray()
    );
    $selected_day = date('Y-m-d');
    if (!empty($days) && !in_array($selected_day, $days)) {
        $selected_day = $days[0];
    }
    if ($request->input('shifts_filter_day')) {
        $selected_day = $request->input('shifts_filter_day');
    }
    $shiftsFilter->setStartTime(parse_date('Y-m-d H:i', $selected_day . ' 00:00'));
    $shiftsFilter->setEndTime(parse_date('Y-m-d H:i', $selected_day . ' 23:59'));

    $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
    $shiftsFilterRenderer->enableDaySelection($days);

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);

    return [
        $room->name,
        Room_view($room, $shiftsFilterRenderer, $shiftCalendarRenderer),
    ];
}

/**
 * Dispatch different room actions.
 *
 * @return array
 */
function rooms_controller(): array
{
    $request = request();
    $action = $request->input('action');
    if (!$request->has('action')) {
        $action = 'list';
    }

    return match ($action) {
        'view'  => room_controller(),
        'list'  => throw_redirect(page_link_to('admin_rooms')),
        default => throw_redirect(page_link_to('admin_rooms')),
    };
}

/**
 * @param Room $room
 * @return string
 */
function room_link(Room $room)
{
    return page_link_to('rooms', ['action' => 'view', 'room_id' => $room->id]);
}

/**
 * Loads room by request param room_id
 *
 * @return Room
 */
function load_room()
{
    if (!test_request_int('room_id')) {
        throw_redirect(page_link_to());
    }

    $room = Room::find(request()->input('room_id'));
    if (!$room) {
        throw_redirect(page_link_to());
    }

    return $room;
}
