import{j as e}from"./jsx-runtime-u17CrQMm.js";import{S as t}from"./stat-card-BrzgDgr-.js";import{U as n}from"./users-C5NjE_v4.js";import"./utils-BQHNewu7.js";import"./card-D9sG6r10.js";import"./badge-dtsZ3OQs.js";import"./index-yvwtsnL6.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-dmJXtalC.js";import"./index-LHNt3CwB.js";import"./skeleton-CuxgNOSS.js";import"./use-reduced-motion-Bb6b8sk-.js";import"./createLucideIcon-Cq8_ABKM.js";const j={title:"Data Display/StatCard",component:t,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{title:{control:"text"},isLoading:{control:"boolean"}}},r={args:{title:"Total Users",value:"12,345",description:"Active accounts this month"}},a={args:{title:"Monthly Revenue",value:"$48,230",description:"vs last month",trend:{value:12.5,label:"12.5%",direction:"up"}}},s={args:{title:"Active Users",value:"3,782",description:"Last 30 days",icon:e.jsx(n,{className:"size-4 text-muted-foreground"}),trend:{value:-3.2,direction:"down"}}},o={args:{title:"Orders",value:"—",isLoading:!0}},i={render:()=>e.jsxs("div",{className:"grid grid-cols-2 gap-4 w-[600px]",children:[e.jsx(t,{title:"Users",value:"12,345",trend:{value:8,direction:"up"}}),e.jsx(t,{title:"Revenue",value:"$48,230",trend:{value:12.5,direction:"up"}}),e.jsx(t,{title:"Churn",value:"2.3%",trend:{value:.5,direction:"down"}}),e.jsx(t,{title:"ARR",value:"$578,760",trend:{value:15,direction:"up"}})]})};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    title: 'Total Users',
    value: '12,345',
    description: 'Active accounts this month'
  }
}`,...r.parameters?.docs?.source}}};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  args: {
    title: 'Monthly Revenue',
    value: '$48,230',
    description: 'vs last month',
    trend: {
      value: 12.5,
      label: '12.5%',
      direction: 'up'
    }
  }
}`,...a.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    title: 'Active Users',
    value: '3,782',
    description: 'Last 30 days',
    icon: <UsersIcon className="size-4 text-muted-foreground" />,
    trend: {
      value: -3.2,
      direction: 'down'
    }
  }
}`,...s.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    title: 'Orders',
    value: '—',
    isLoading: true
  }
}`,...o.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  render: () => <div className="grid grid-cols-2 gap-4 w-[600px]">
            <StatCard title="Users" value="12,345" trend={{
      value: 8,
      direction: 'up'
    }} />
            <StatCard title="Revenue" value="$48,230" trend={{
      value: 12.5,
      direction: 'up'
    }} />
            <StatCard title="Churn" value="2.3%" trend={{
      value: 0.5,
      direction: 'down'
    }} />
            <StatCard title="ARR" value="$578,760" trend={{
      value: 15,
      direction: 'up'
    }} />
        </div>
}`,...i.parameters?.docs?.source}}};const C=["Default","WithTrend","WithIcon","Loading","Grid"];export{r as Default,i as Grid,o as Loading,s as WithIcon,a as WithTrend,C as __namedExportsOrder,j as default};
