/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const ordersRoutes = {
  path: '/orders',
  component: Layout,
  redirect: '/orders/list',
  name: 'Orders',
  meta: {
    title: 'Orders',
    icon: 'orders',
    permissions: ['view menu order'],
  },
  children: [
    {
      path: '/list',
      component: () => import('@/views/orders/List'),
      name: 'listOrder',
      meta: { title: 'listOrder' },
    },
    {
      path: '/create',
      component: () => import('@/views/orders/Create'),
      name: 'createOrder',
      meta: { title: 'createOrder' },
    },
    {
      path: '/edit/:id(\\d+)',
      component: () => import('@/views/orders/Edit'),
      name: 'editOrder',
      meta: { title: 'editOrder' },
    },
    {
      path: 'upload-excel',
      component: () => import('@/views/excel/UploadExcel'),
      name: 'UploadExcel',
      meta: { title: 'uploadExcel' },
    },
  ],
};

export default ordersRoutes;
