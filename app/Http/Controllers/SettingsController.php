<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadStatusRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * My profile, plus the lead-status list for admins.
     */
    public function edit(Request $request)
    {
        $editingStatus = null;
        $statusId = $request->query('edit_status');

        if ($statusId && $request->user()->canManageStatuses()) {
            $editingStatus = LeadStatus::query()->findOrFail($statusId);
        }

        $statuses = LeadStatus::query()->orderBy('name')->get();

        return view('settings.edit', [
            'user' => $request->user(),
            'statuses' => $statuses,
            'editingStatus' => $editingStatus,
        ]);
    }

    /**
     * Save the signed-in user's name, email, and optional new password.
     * Role is not on this form.
     */
    public function updateProfile(ProfileRequest $request)
    {
        $user = $request->user();
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        return redirect()->route('settings.edit')->with('success', 'Profile updated.');
    }

    /**
     * Add a lead status.
     */
    public function storeStatus(LeadStatusRequest $request)
    {
        abort_unless($request->user()->canManageStatuses(), 403);

        $status = new LeadStatus;
        $status->name = $request->input('name');
        $status->color = $request->input('color');
        $status->save();

        return redirect()->route('settings.edit')->with('success', 'Status created.');
    }

    /**
     * Rename a status or change its colour.
     */
    public function updateStatus(LeadStatusRequest $request, LeadStatus $status)
    {
        abort_unless($request->user()->canManageStatuses(), 403);

        $status->name = $request->input('name');
        $status->color = $request->input('color');
        $status->save();

        return redirect()->route('settings.edit')->with('success', 'Status updated.');
    }

    /**
     * Delete a status. Leads that used it keep their other details and lose the status.
     */
    public function destroyStatus(Request $request, LeadStatus $status)
    {
        abort_unless($request->user()->canManageStatuses(), 403);

        $status->delete();

        return redirect()->route('settings.edit')->with('success', 'Status deleted.');
    }
}
