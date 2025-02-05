
<li class="sidebar-header">
    디자인 및 테마
</li>

<li class="sidebar-item">
    <a class="sidebar-link" href="/{{ prefix('admin') }}/theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-palette" viewBox="0 0 16 16">
            <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
            <path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7"/>
        </svg>

        <span class="align-middle">테마</span>
        {{-- <span class="badge badge-sidebar-primary">New</span> --}}
    </a>
</li>

<li class="sidebar-item">
    <a data-bs-target="#ui" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            data-lucide="grid" class="lucide lucide-grid align-middle">
            <rect width="18" height="18" x="3" y="3" rx="2"></rect>
            <path d="M3 9h18"></path>
            <path d="M3 15h18"></path>
            <path d="M9 3v18"></path>
            <path d="M15 3v18"></path>
        </svg> <span class="align-middle">UI Elements</span>
    </a>
    <ul id="ui" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-alerts">Alerts</a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-buttons">Buttons</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-cards">Cards</a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-carousel">Carousel</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-embed-video">Embed
                Video</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-general">General
                <span class="badge badge-sidebar-primary">10+</span></a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-grid">Grid</a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-modals">Modals</a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-offcanvas">Offcanvas</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-placeholders">Placeholders</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-tabs">Tabs</a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link" href="/ui-typography">Typography</a></li>
    </ul>
</li>
<li class="sidebar-item">
    <a data-bs-target="#icons" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            data-lucide="heart" class="lucide lucide-heart align-middle">
            <path
                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
            </path>
        </svg> <span class="align-middle">Icons</span>
        <span class="badge badge-sidebar-primary">1500+</span>
    </a>
    <ul id="icons" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item"><a class="sidebar-link" href="/icons-lucide">Lucide</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/icons-font-awesome">Font Awesome</a></li>
    </ul>
</li>
<li class="sidebar-item">
    <a data-bs-target="#forms" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            data-lucide="check-square" class="lucide lucide-check-square align-middle">
            <path d="m9 11 3 3L22 4"></path>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg> <span class="align-middle">Forms</span>
    </a>
    <ul id="forms" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item"><a class="sidebar-link" href="/forms-layouts">Layouts</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/forms-basic-inputs">Basic Inputs</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/forms-input-groups">Input Groups</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="/forms-floating-labels">Floating Labels</a></li>
    </ul>
</li>
<li class="sidebar-item">
    <a class="sidebar-link" href="/tables">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            data-lucide="list" class="lucide lucide-list align-middle">
            <line x1="8" x2="21" y1="6" y2="6">
            </line>
            <line x1="8" x2="21" y1="12" y2="12">
            </line>
            <line x1="8" x2="21" y1="18" y2="18">
            </line>
            <line x1="3" x2="3.01" y1="6" y2="6">
            </line>
            <line x1="3" x2="3.01" y1="12" y2="12">
            </line>
            <line x1="3" x2="3.01" y1="18" y2="18">
            </line>
        </svg> <span class="align-middle">Tables</span>
    </a>
</li>
<li class="sidebar-item">
    <a data-bs-target="#multi" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            data-lucide="share-2" class="lucide lucide-share-2 align-middle">
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" x2="15.42" y1="13.51" y2="17.49">
            </line>
            <line x1="15.41" x2="8.59" y1="6.51" y2="10.49">
            </line>
        </svg> <span class="align-middle">Multi Level</span>
    </a>
    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a data-bs-target="#multi-2" data-bs-toggle="collapse" class="sidebar-link collapsed">
                Two Levels
            </a>
            <ul id="multi-2" class="sidebar-dropdown list-unstyled collapse">
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-target="#">Item 1</a>
                    <a class="sidebar-link" data-bs-target="#">Item 2</a>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a data-bs-target="#multi-3" data-bs-toggle="collapse" class="sidebar-link collapsed">
                Three Levels
            </a>
            <ul id="multi-3" class="sidebar-dropdown list-unstyled collapse">
                <li class="sidebar-item">
                    <a data-bs-target="#multi-3-1" data-bs-toggle="collapse" class="sidebar-link collapsed">
                        Item 1
                    </a>
                    <ul id="multi-3-1" class="sidebar-dropdown list-unstyled collapse">
                        <li class="sidebar-item">
                            <a class="sidebar-link" data-bs-target="#">Item 1</a>
                            <a class="sidebar-link" data-bs-target="#">Item 2</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-target="#">Item 2</a>
                </li>
            </ul>
        </li>
    </ul>
</li>
